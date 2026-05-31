<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\RemovalRequests\Tables;

use App\Filament\Tables\MorphColumn;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RemovalRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'clip',
                'broadcaster',
                'claimer',
                'resolver',
            ]))
            ->defaultSort('id')
            ->columns([
                MorphColumn::make('clip'),
                MorphColumn::make('broadcaster'),

                TextColumn::make('status')
                    ->label('admin/resources/removal-requests.table.columns.status')
                    ->translateLabel(),

                MorphColumn::make('claimer')
                    ->toggleable(isToggledHiddenByDefault: true),
                MorphColumn::make('resolver')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('claimed_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('resolved_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
