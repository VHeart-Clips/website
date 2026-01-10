<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compilations\RelationManagers;

use App\Enums\Clips\CompilationClipStatus;
use App\Filament\Resources\Clips\ClipResource;
use App\Models\Clip;
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
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

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

                TextColumn::make('broadcaster.name')
                    ->searchable(),

                TextColumn::make('creator.name')
                    ->label('Clipper')
                    ->searchable(),

                TextColumn::make('submitter.name')
                    ->searchable(),

                TextColumn::make('game.title')
                    ->searchable(),

                TextColumn::make('duration')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => gmdate('i:s', (int) round($state)))
                    ->sortable(),

                TextColumn::make('claimer.name'),

                SelectColumn::make('status')
                    ->options(CompilationClipStatus::class)
                    ->default(CompilationClipStatus::Pending)
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
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
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
                        // Fixed: Access pivot data specifically
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

                        ->openUrlInNewTab(),

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
