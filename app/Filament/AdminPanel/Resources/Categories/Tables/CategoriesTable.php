<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Categories\Tables;

use App\Enums\Filament\LucideIcon;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $query->withCount('clips');
            })
            ->columns([
                ImageColumn::make('box_art')
                    ->label('admin/resources/categories.table.columns.box_art')
                    ->translateLabel()
                    ->getStateUsing(fn (Category $game): ?string => $game->getBoxArt())
                    ->imageHeight(100)
                    ->width(75)
                    ->grow(false),

                TextColumn::make('title')
                    ->searchable()
                    ->label('admin/resources/categories.table.columns.title')
                    ->translateLabel(),

                TextColumn::make('clips_count')
                    ->label('admin/resources/categories.table.columns.clips_count')
                    ->translateLabel()
                    ->sortable(),

                IconColumn::make('is_banned')
                    ->label('admin/resources/categories.table.columns.is_banned')
                    ->translateLabel()
                    ->falseColor('success')
                    ->trueColor('danger')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_banned')
                    ->label('admin/resources/categories.table.filters.is_banned')
                    ->translateLabel(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('ban')
                        ->authorize('update')
                        ->label('admin/resources/categories.table.actions.ban')
                        ->translateLabel()
                        ->icon(LucideIcon::Lock)
                        ->color('danger')
                        ->hidden(fn (Category $record): bool => $record->is_banned)
                        ->requiresConfirmation()
                        ->action(function (Category $category): void {
                            $category->is_banned = true;
                            $category->save();
                        }),
                    Action::make('unban')
                        ->authorize('update')
                        ->label('admin/resources/categories.table.actions.unban')
                        ->translateLabel()
                        ->icon(LucideIcon::LockOpen)
                        ->color('success')
                        ->hidden(fn (Category $record): bool => ! $record->is_banned)
                        ->requiresConfirmation()
                        ->action(function (Category $category): void {
                            $category->is_banned = false;
                            $category->save();
                        }),
                ]),
            ])
            ->toolbarActions([
            ]);
    }
}
