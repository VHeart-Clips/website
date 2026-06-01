<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Tables;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\Clips\CompilationType;
use App\Models\Clip\Compilation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompilationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('admin/resources/compilations.table.columns.title')
                    ->translateLabel()
                    ->wrap()
                    ->searchable(),
                TextColumn::make('clips_count')
                    ->label('admin/resources/compilations.table.columns.clips_count')
                    ->fontFamily(FontFamily::Mono)
                    ->counts('clips')
                    ->translateLabel()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('clips_count_pending')
                    ->counts(['clips as clips_count_pending' => fn (Builder $builder) => $builder->where('claim_status', CompilationClipClaimStatus::Pending)])
                    ->label('admin/resources/compilations.table.columns.clips_count_pending')
                    ->color(fn (int $state): ?string => $state > 0 ? 'danger' : null)
                    ->fontFamily(FontFamily::Mono)
                    ->translateLabel()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('clips_count_in_progress')
                    ->counts(['clips as clips_count_in_progress' => fn (Builder $builder) => $builder->where('claim_status', CompilationClipClaimStatus::InProgress)])
                    ->label('admin/resources/compilations.table.columns.clips_count_in_progress')
                    ->color(fn (int $state): ?string => $state > 0 ? 'warning' : null)
                    ->fontFamily(FontFamily::Mono)
                    ->translateLabel()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('clips_count_completed')
                    ->counts(['clips as clips_count_completed' => fn (Builder $builder) => $builder->where('claim_status', CompilationClipClaimStatus::Completed)])
                    ->label('admin/resources/compilations.table.columns.clips_count_completed')
                    ->fontFamily(FontFamily::Mono)
                    ->translateLabel()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('clips_sum_duration')
                    ->formatStateUsing(fn (float $state): string => gmdate('i:s', (int) round($state)))
                    ->label('admin/resources/compilations.table.columns.clips_sum_duration')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sum('clips', 'duration')
                    ->placeholder('No Clips :(')
                    ->fontFamily(FontFamily::Mono)
                    ->translateLabel()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('clips_est_duration')
                    ->formatStateUsing(fn (float $state): string => gmdate('i:s', (int) round($state)))
                    ->label('admin/resources/compilations.table.columns.clips_est_duration')
                    ->sum('clips as clips_est_duration', 'duration')
                    ->toggleable(isToggledHiddenByDefault: true) // hidden by default because its likely very off, but nice to have i guess
                    ->placeholder('No Clips :(')
                    ->fontFamily(FontFamily::Mono)
                    ->translateLabel()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('clips_avg_duration')
                    ->label('admin/resources/compilations.table.columns.clips_avg_duration')
                    ->formatStateUsing(fn (float $state): string => round($state).'s')
                    ->avg('clips', 'duration')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('No Clips :(')
                    ->fontFamily(FontFamily::Mono)
                    ->translateLabel()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('progress')
                    ->label('admin/resources/compilations.table.columns.progress')
                    ->getStateUsing(fn (Compilation $record): float|int => $record->clips_count > 0
                        ? round(($record->clips_count_completed / $record->clips_count) * 100)
                        : 0
                    )
                    ->suffix('%')
                    ->placeholder('No Clips :(')
                    ->fontFamily(FontFamily::Mono)
                    ->translateLabel()
                    ->alignCenter()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('admin/resources/compilations.table.columns.created_by')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->translateLabel(),
                TextColumn::make('status')
                    ->label('admin/resources/compilations.table.columns.status')
                    ->toggleable()
                    ->translateLabel()
                    ->badge(),
                TextColumn::make('type')
                    ->label('admin/resources/compilations.table.columns.type')
                    ->translateLabel()
                    ->badge()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('compilations.id', 'desc')
            ->filtersFormWidth(Width::Large)
            ->filtersFormColumns(2)
            ->filters([
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('admin/resources/compilations.table.filters.created_by')
                    ->translateLabel()
                    ->columnSpanFull(),
                SelectFilter::make('status')
                    ->multiple()
                    ->options(CompilationStatus::class),
                SelectFilter::make('type')
                    ->multiple()
                    ->options(CompilationType::class),
                Filter::make('created_at')
                    ->columnSpanFull()
                    ->schema([
                        Fieldset::make(__('admin/resources/compilations.table.filters.creation_date'))
                            ->schema([
                                DatePicker::make('created_from'),
                                DatePicker::make('created_until'),
                            ]),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        )),
                TernaryFilter::make('has_youtube')
                    ->label('admin/resources/compilations.table.filters.youtube_link')
                    ->translateLabel()
                    ->nullable()
                    ->placeholder(__('admin/resources/compilations.table.filters.all'))
                    ->trueLabel(__('admin/resources/compilations.table.filters.with'))
                    ->falseLabel(__('admin/resources/compilations.table.filters.without'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('youtube_url'),
                        false: fn (Builder $query) => $query->whereNull('youtube_url'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                TernaryFilter::make('has_clips')
                    ->label('admin/resources/compilations.table.filters.clips')
                    ->translateLabel()
                    ->nullable()
                    ->placeholder(__('admin/resources/compilations.table.filters.all'))
                    ->trueLabel(__('admin/resources/compilations.table.filters.with'))
                    ->falseLabel(__('admin/resources/compilations.table.filters.without'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('clips'),
                        false: fn (Builder $query) => $query->whereDoesntHave('clips'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                TrashedFilter::make()->columnSpanFull(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorizeIndividualRecords(),
                    ForceDeleteBulkAction::make()->authorizeIndividualRecords(),
                    RestoreBulkAction::make()->authorizeIndividualRecords(),
                ]),
            ]);
    }
}
