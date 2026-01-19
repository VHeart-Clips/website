<?php

namespace App\Filament\Resources\Clips\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('twitch_id')
                    ->label('admin/resources/clips.table.columns.twitch_id')
                    ->translateLabel()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                ImageColumn::make('thumbnail_url')
                    ->label('admin/resources/clips.table.columns.thumbnail')
                    ->translateLabel()
                    ->height(100),
                TextColumn::make('title')
                    ->label('admin/resources/clips.table.columns.title')
                    ->translateLabel()
                    ->wrap()
                    ->searchable(),
                TextColumn::make('broadcaster.name')
                    ->label('admin/resources/clips.table.columns.broadcaster')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('creator.name')
                    ->label('admin/resources/clips.table.columns.clipper')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('submitter.name')
                    ->label('admin/resources/clips.table.columns.submitter')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('game.title')
                    ->label('admin/resources/clips.table.columns.category')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('duration')
                    ->label('admin/resources/clips.table.columns.duration')
                    ->translateLabel()
                    ->numeric()
                    ->formatStateUsing(function ($state) {
                        $totalSeconds = (int) round($state);

                        $minutes = intdiv($totalSeconds, 60);
                        $seconds = $totalSeconds % 60;

                        return sprintf('%d:%02d', $minutes, $seconds);
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label('admin/resources/clips.table.columns.status')
                    ->translateLabel()
                    ->searchable(),
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
                TextColumn::make('updated_at')
                    ->label('admin/resources/clips.table.columns.updated_at')
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('admin/resources/clips.table.columns.deleted_at')
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
