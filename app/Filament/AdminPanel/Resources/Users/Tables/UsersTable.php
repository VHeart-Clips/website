<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\Tables;

use App\Filament\AdminPanel\Actions\Ban\BanAction;
use Filament\Actions\BulkActionGroup;
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
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $query->whereNot('id', 0);
            })
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
                    ->searchable()
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
                BanAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make()->label('Restore User'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords()
                        ->label('Disable Users'),
                    ForceDeleteBulkAction::make()
                        ->authorizeIndividualRecords(),
                    RestoreBulkAction::make()
                        ->authorizeIndividualRecords()
                        ->label('Restore Users'),
                ]),
            ]);
    }
}
