<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compilations\RelationManagers;

use App\Enums\Clips\CompilationClipStatus;
use App\Filament\Resources\Clips\ClipResource;
use App\Models\Clip;
use App\Models\User;
use App\Services\Twitch\Data\ClipDownloadDto;
use App\Services\Twitch\Exceptions\TwitchApiException;
use App\Services\Twitch\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Component;

class ClipsRelationManager extends RelationManager
{
    protected static string $relationship = 'clips';

    protected static ?string $relatedResource = ClipResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('twitch_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                ImageColumn::make('thumbnail_url')
                    ->label('Thumbnail')
                    ->imageHeight(100),

                TextColumn::make('title')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('broadcaster.name'),
                TextColumn::make('creator.name')
                    ->label('Clipper'),
                TextColumn::make('submitter.name'),
                TextColumn::make('game.title'),

                TextColumn::make('duration')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => gmdate('i:s', (int) round($state)))
                    ->sortable(),

                TextColumn::make('claimer.name'),

                SelectColumn::make('status')
                    ->options(CompilationClipStatus::class)
                    ->default(CompilationClipStatus::Pending)
                    ->disabled(function (Clip $record): bool {
                        return $record->pivot->claimed_by !== auth()->id();
                    })
                    ->selectablePlaceholder(false)
                    ->updateStateUsing(function (Clip $record, $state) {
                        $record->pivot->update(['status' => $state]);

                        return $state;
                    }),

                IconColumn::make('is_anonymous')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('broadcaster')
                    ->relationship('broadcaster', 'name', fn (Builder $query) => $query->whereIn('id',
                        $this->getOwnerRecord()->clips()->pluck('broadcaster_id')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Broadcaster'),
                SelectFilter::make('creator')
                    ->relationship('creator', 'name', fn (Builder $query) => $query->whereIn('id',
                        $this->getOwnerRecord()->clips()->pluck('creator_id')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Clipper'),
                SelectFilter::make('submitter')
                    ->relationship('submitter', 'name', fn (Builder $query) => $query->whereIn('id',
                        $this->getOwnerRecord()->clips()->pluck('submitter_id')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Submitter'),
                SelectFilter::make('claimer')
                    ->label('Claimer')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => User::query()
                        ->whereIn('id', $this->getOwnerRecord()->clips()->newPivotStatement()
                            ->where('compilation_id', $this->getOwnerRecord()->getKey())
                            ->pluck('claimed_by'))
                        ->pluck('name', 'id')
                        ->prepend('None / Unclaimed', 'null'))
                    ->query(function (Builder $query, array $data) {
                        $values = $data['values'] ?? [];
                        if (empty($values)) {
                            return;
                        }

                        $query->where(function (Builder $query) use ($values) {
                            $ids = array_diff($values, ['null']);

                            if (in_array('null', $values, true)) {
                                $query->whereNull('clip_compilation.claimed_by');
                            }

                            if (! empty($ids)) {
                                $query->orWhereIn('clip_compilation.claimed_by', $ids);
                            }
                        });
                    }),
                SelectFilter::make('game')
                    ->relationship('game', 'title',
                        fn (Builder $query) => $query->whereIn('id', $this->getOwnerRecord()->clips()->pluck('game_id')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Game'),
                SelectFilter::make('status')
                    ->multiple()
                    ->options(CompilationClipStatus::class),
            ])
            ->filtersFormColumns(2)
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('status')
                            ->options(CompilationClipStatus::class)
                            ->default(CompilationClipStatus::Pending)
                            ->required(),
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('claim')
                        ->icon(Heroicon::LockClosed)
                        ->rateLimit(5)
                        ->hidden(fn (Clip $record) => $record->pivot->claimed_by === auth()->id())
                        ->requiresConfirmation(fn (Clip $record) => ! is_null($record->pivot->claimed_by))
                        ->action(function (Clip $clip) {
                            $lockKey = 'claim-clip-'.$clip->pivot->compilation_id.':'.$clip->id;

                            Cache::lock($lockKey, 10)->get(function () use ($clip) {
                                $clip->pivot->update([
                                    'claimed_by' => auth()->id(),
                                ]);

                                Notification::make()
                                    ->title('Clip Claimed')
                                    ->success()
                                    ->body('You have successfully claimed this clip.')
                                    ->send();

                                return true;
                            });
                        }),

                    Action::make('unclaim')
                        ->icon(Heroicon::LockOpen)
                        ->hidden(fn (Clip $record) => $record->pivot->claimed_by !== auth()->id())
                        ->requiresConfirmation()
                        ->action(function (Clip $clip) {
                            $clip->pivot->update([
                                'claimed_by' => null,
                            ]);

                            Notification::make()
                                ->title('Clip Unclaimed')
                                ->success()
                                ->send();
                        }),

                    Action::make('download')
                        ->icon(Heroicon::ArrowDownTray)
                        ->disabled(fn (Clip $record) => $record->pivot->claimed_by !== auth()->id())
                        ->action(function (Clip $clip, TwitchService $twitchService, Component $livewire) {
                            $broadCaster = $clip->broadcaster;

                            if (! $broadCaster) {
                                Notification::make()
                                    ->title('Cannot download Clip')
                                    ->body('Broadcaster is not available.')
                                    ->danger()
                                    ->send();

                                return false;
                            }

                            try {
                                $response = $twitchService->asUser($clip->broadcaster)->get(TwitchEndpoints::GetClipsDownload,
                                    [
                                        'editor_id' => $clip->broadcaster_id,
                                        'broadcaster_id' => $clip->broadcaster_id,
                                        'clip_id' => $clip->twitch_id,
                                    ]);
                                /** @var ClipDownloadDto $download */
                                $download = array_first($response);

                                if (! $download) {
                                    Notification::make()
                                        ->title('Cannot download Clip')
                                        ->body('Clip was not found.')
                                        ->danger()
                                        ->send();

                                    return false;
                                }

                                // thanks to cors we are very limited, user still has to download it manually
                                // alternative would be downloading it to server and serving it as a proxy
                                // that way we may have a permanent copy and cache, but also more traffic
                                $livewire->js("window.open('{$download->landscape_download_url}', '_blank')");
                            } catch (TwitchApiException $e) {
                                Notification::make()
                                    ->title('Can not download Clip')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();

                                return false;
                            }

                            return true;
                        }),
                    Action::make('copy_cutter_optimized_name')
                        ->label('Copy Filename')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->color('gray')
                        ->tooltip('Copy standardized filename for editors')
                        ->action(function (Clip $clip, $livewire) {
                            $title = Str::limit($clip->title, 50, '');

                            $filename = "[{$clip->id}] {$clip->broadcaster->name} - {$clip->game->title} - {$title}.mp4";
                            $livewire->js("window.navigator.clipboard.writeText('{$filename}');");

                            Notification::make()
                                ->title('Filename Copied')
                                ->body($filename)
                                ->success()
                                ->send();
                        }),

                    DetachAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ])
            ->openRecordUrlInNewTab();
    }
}
