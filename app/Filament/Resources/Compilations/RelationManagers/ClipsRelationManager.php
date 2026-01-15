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
                    ->label('admin/resources/clips.table.columns.twitch_id')
                    ->translateLabel()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                ImageColumn::make('thumbnail_url')
                    ->label('admin/resources/clips.table.columns.thumbnail')
                    ->translateLabel()
                    ->imageHeight(100),

                TextColumn::make('title')
                    ->label('admin/resources/clips.table.columns.title')
                    ->translateLabel()
                    ->wrap()
                    ->searchable(),

                TextColumn::make('broadcaster.name')
                    ->label('admin/resources/clips.table.columns.broadcaster')
                    ->translateLabel(),

                TextColumn::make('creator.name')
                    ->label('admin/resources/clips.table.columns.clipper')
                    ->translateLabel(),

                TextColumn::make('submitter.name')
                    ->label('admin/resources/clips.table.columns.submitter')
                    ->translateLabel(),

                TextColumn::make('game.title')
                    ->label('admin/resources/clips.table.columns.category')
                    ->translateLabel(),

                TextColumn::make('duration')
                    ->label('admin/resources/clips.table.columns.duration')
                    ->translateLabel()
                    ->numeric()
                    ->formatStateUsing(fn ($state) => gmdate('i:s', (int) round($state)))
                    ->sortable(),

                TextColumn::make('claimer.name')
                    ->label('admin/resources/compilations.relation_managers.clips.columns.claimer')
                    ->translateLabel(),

                SelectColumn::make('pivot.status')
                    ->label('admin/resources/compilations.relation_managers.clips.columns.status')
                    ->translateLabel()
                    ->options(CompilationClipStatus::class)
                    ->native(false)
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
                    ->label('admin/resources/clips.table.columns.is_anonymous')
                    ->translateLabel()
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('admin/resources/clips.table.columns.created_at')
                    ->translateLabel()
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
                    ->label('admin/resources/compilations.relation_managers.clips.filters.broadcaster')
                    ->translateLabel(),
                SelectFilter::make('creator')
                    ->relationship('creator', 'name', fn (Builder $query) => $query->whereIn('id',
                        $this->getOwnerRecord()->clips()->pluck('creator_id')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/compilations.relation_managers.clips.filters.clipper')
                    ->translateLabel(),
                SelectFilter::make('submitter')
                    ->relationship('submitter', 'name', fn (Builder $query) => $query->whereIn('id',
                        $this->getOwnerRecord()->clips()->pluck('submitter_id')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/compilations.relation_managers.clips.filters.submitter')
                    ->translateLabel(),
                SelectFilter::make('claimer')
                    ->label('admin/resources/compilations.relation_managers.clips.filters.claimer')
                    ->translateLabel()
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => User::query()
                        ->whereIn('id', $this->getOwnerRecord()->clips()->newPivotStatement()
                            ->where('compilation_id', $this->getOwnerRecord()->getKey())
                            ->pluck('claimed_by'))
                        ->pluck('name', 'id')
                        ->prepend(__('admin/resources/compilations.relation_managers.clips.filters.claimer_option_none'), 'null'))
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
                    ->label('admin/resources/compilations.relation_managers.clips.filters.game')
                    ->translateLabel(),
                SelectFilter::make('status')
                    ->label('admin/resources/compilations.relation_managers.clips.filters.status')
                    ->translateLabel()
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
                            ->label('admin/resources/compilations.relation_managers.clips.columns.status')
                            ->translateLabel()
                            ->options(CompilationClipStatus::class)
                            ->default(CompilationClipStatus::Pending)
                            ->required(),
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('claim')
                        ->label('admin/resources/compilations.relation_managers.clips.actions.claim')
                        ->translateLabel()
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
                                    ->title(__('admin/resources/compilations.relation_managers.clips.notifications.claimed_title'))
                                    ->success()
                                    ->body(__('admin/resources/compilations.relation_managers.clips.notifications.claimed_body'))
                                    ->send();

                                return true;
                            });
                        }),

                    Action::make('unclaim')
                        ->label('admin/resources/compilations.relation_managers.clips.actions.unclaim')
                        ->translateLabel()
                        ->icon(Heroicon::LockOpen)
                        ->hidden(fn (Clip $record) => $record->pivot->claimed_by !== auth()->id())
                        ->requiresConfirmation()
                        ->action(function (Clip $clip) {
                            $clip->pivot->update([
                                'claimed_by' => null,
                            ]);

                            Notification::make()
                                ->title(__('admin/resources/compilations.relation_managers.clips.notifications.unclaimed_title'))
                                ->success()
                                ->send();
                        }),

                    Action::make('download')
                        ->label('admin/resources/clips.actions.download')
                        ->translateLabel()
                        ->icon(Heroicon::ArrowDownTray)
                        ->disabled(fn (Clip $record) => $record->pivot->claimed_by !== auth()->id())
                        ->action(function (Clip $clip, TwitchService $twitchService, Component $livewire) {
                            $broadCaster = $clip->broadcaster;

                            if (! $broadCaster || empty($broadCaster->twitch_refresh_token) || $broadCaster->clip_permission === false) {
                                Notification::make()
                                    ->title(__('admin/resources/compilations.relation_managers.clips.notifications.download_error_title'))
                                    ->body(__('admin/resources/compilations.relation_managers.clips.notifications.download_error_broadcaster'))
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
                                        ->title(__('admin/resources/compilations.relation_managers.clips.notifications.download_error_title'))
                                        ->body(__('admin/resources/compilations.relation_managers.clips.notifications.download_error_not_found'))
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
                                    ->title(__('admin/resources/compilations.relation_managers.clips.notifications.download_error_title'))
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();

                                return false;
                            }

                            return true;
                        }),
                    Action::make('copy_cutter_optimized_name')
                        ->label('admin/resources/compilations.relation_managers.clips.actions.copy_filename')
                        ->translateLabel()
                        ->icon('heroicon-o-clipboard-document-list')
                        ->color('gray')
                        ->tooltip(__('admin/resources/compilations.relation_managers.clips.actions.copy_filename_tooltip'))
                        ->action(function (Clip $clip, $livewire) {
                            $title = Str::limit($clip->title, 50, '');

                            $filename = "[{$clip->id}] {$clip->broadcaster->name} - {$clip->game->title} - {$title}.mp4";
                            $livewire->js("window.navigator.clipboard.writeText('{$filename}');");

                            Notification::make()
                                ->title(__('admin/resources/compilations.relation_managers.clips.notifications.filename_copied'))
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
