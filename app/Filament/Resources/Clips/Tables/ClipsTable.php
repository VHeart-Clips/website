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
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                ImageColumn::make('thumbnail_url')->label('Thumbnail')->height(100),
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
                    ->formatStateUsing(function ($state) {
                        $totalSeconds = (int) round($state);

                        $minutes = intdiv($totalSeconds, 60);
                        $seconds = $totalSeconds % 60;

                        return sprintf('%d:%02d', $minutes, $seconds);
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                IconColumn::make('is_anonymous')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
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
