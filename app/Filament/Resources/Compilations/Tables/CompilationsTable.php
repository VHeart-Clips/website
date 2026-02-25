<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compilations\Tables;

use App\Enums\Clips\CompilationStatus;
use App\Enums\Clips\CompilationType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
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
                TextColumn::make('user.name')
                    ->label('admin/resources/compilations.table.columns.created_by')
                    ->translateLabel(),
                TextColumn::make('status')
                    ->label('admin/resources/compilations.table.columns.status')
                    ->translateLabel()
                    ->badge(),
                TextColumn::make('type')
                    ->label('admin/resources/compilations.table.columns.type')
                    ->translateLabel()
                    ->badge()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
