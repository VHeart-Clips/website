<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class RolesTable
{
    use Translatable;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('admin/resources/roles.table.columns.name')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('desc')
                    ->label('admin/resources/roles.table.columns.desc')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('weight')
                    ->label('admin/resources/roles.table.columns.weight')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                IconColumn::make('public')
                    ->label('admin/resources/roles.table.columns.public')
                    ->translateLabel()
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('admin/resources/roles.table.columns.created_at')
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('admin/resources/roles.table.columns.updated_at')
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
