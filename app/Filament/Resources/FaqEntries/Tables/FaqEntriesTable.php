<?php

declare(strict_types=1);

namespace App\Filament\Resources\FaqEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class FaqEntriesTable
{
    use Translatable;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('admin/resources/faq-entry.table.columns.question.label')
                    ->translateLabel()
                    ->placeholder(__('admin/resources/faq-entry.table.columns.question.placeholder')),
                TextColumn::make('published_at')
                    ->label('admin/resources/faq-entry.table.columns.published_at.label')
                    ->translateLabel()
                    ->placeholder(__('admin/resources/faq-entry.table.columns.published_at.placeholder'))
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('order')
            ->reorderable('order');
    }
}
