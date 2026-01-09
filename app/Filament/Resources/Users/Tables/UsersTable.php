<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                ImageColumn::make('avatar_url')
                    ->label('Avatar')->square(),
                TextColumn::make('name')
                    ->label('Name'),
                TextColumn::make('roles.name')->label('Roles')->toggleable()->badge(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('roles')
                    ->preload()
                    ->relationship('roles', 'name')
                    ->multiple(),

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->label('Disable User'),
                ForceDeleteAction::make(),
                RestoreAction::make()->label('Restore User'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Disable Users'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make()->label('Restore Users'),
                ]),
            ]);
    }
}
