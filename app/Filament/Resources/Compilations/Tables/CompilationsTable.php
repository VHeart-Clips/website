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
                TextColumn::make('title')->wrap()->label('Title')->searchable(),
                TextColumn::make('user.name')->label('Created By'),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('type')->label('Type')->badge()->toggleable()->toggledHiddenByDefault(),
            ])
            ->filtersFormWidth(Width::Large)
            ->filtersFormColumns(2)
            ->filters([
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Created By')
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
                        Fieldset::make('Filter by Creation Date')->schema([
                            DatePicker::make('created_from'),
                            DatePicker::make('created_until'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                TernaryFilter::make('has_youtube')
                    ->label('Youtube Link')
                    ->nullable()
                    ->placeholder('All')
                    ->trueLabel('With')
                    ->falseLabel('Without')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('youtube_url'),
                        false: fn (Builder $query) => $query->whereNull('youtube_url'),
                        blank: fn (Builder $query) => $query,
                    ),
                TernaryFilter::make('has_clips')
                    ->label('Clips')
                    ->nullable()
                    ->placeholder('All')
                    ->trueLabel('With')
                    ->falseLabel('Without')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('clips'),
                        false: fn (Builder $query) => $query->whereDoesntHave('clips'),
                        blank: fn (Builder $query) => $query,
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
