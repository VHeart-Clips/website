<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\ReportCategories\RelationManagers;

use App\Filament\AdminPanel\Resources\ReportCategories\Pages\ViewReportCategory;
use App\Filament\AdminPanel\Resources\ReportCategories\ReportCategoryResource;
use App\Models\ReportCategory;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subCategories';

    /**
     * @param  ReportCategory  $ownerRecord
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->parent_id === null && $pageClass === ViewReportCategory::class;
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('sort_order')
            ->paginated(false)
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderBy('sort_order')
                ->orderBy('id'))
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (ReportCategory $record): string => ReportCategoryResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
