<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\ReportCategories\Tables;

use App\Enums\Reports\ReportCategoryDetailsType;
use App\Enums\Reports\ReportCategoryReportableType;
use App\Filament\Actions\ResourceLinkAction;
use App\Models\ReportCategory;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->groups([
                Group::make('parent_id')
                    ->label('Parent Category')
                    ->getTitleFromRecordUsing(fn (ReportCategory $record): string => $record->parentCategory?->name ?? 'No parent')
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('name')
                    ->description(fn (ReportCategory $record): ?string => $record->summary)
                    ->wrap()
                    ->weight(FontWeight::Medium)
                    ->searchable(),
                TextColumn::make('parentCategory.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('None')
                    ->label('Parent')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderByRaw($direction === 'asc' ? 'parent_id asc nulls first' : 'parent_id desc nulls last')
                        ->orderBy('sort_order')
                        ->orderBy('id')
                    ),
                TextColumn::make('reportable_types')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('All types')
                    ->badge(),
                IconColumn::make('is_note')
                    ->label('Is Note')
                    ->boolean(),
                TextColumn::make('details_type')
                    ->label('Details')
                    ->placeholder('Disabled')
                    ->badge(),
                TextColumn::make('sort_order')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->numeric(),

                TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_parent')
                    ->label('Has subcategories')
                    ->queries(
                        true: fn (Builder $query) => $query
                            ->whereHas('subCategories', fn (Builder $q) => $q->withTrashed()),
                        false: fn (Builder $query) => $query
                            ->whereDoesntHave('subCategories', fn (Builder $q) => $q->withTrashed()),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                SelectFilter::make('parent_id')
                    ->label('Parent')
                    ->relationship('parentCategory', 'name', fn (Builder $query) => $query
                        ->whereNull('parent_id')
                        ->whereHas('subCategories', fn (Builder $q) => $q->withTrashed())
                        ->limit(5)
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('details_type')
                    ->label('Details')
                    ->options(ReportCategoryDetailsType::class),
                SelectFilter::make('reportable_types')
                    ->label('Reportable type')
                    ->options(ReportCategoryReportableType::class)
                    ->query(fn (Builder $query, array $data) => filled($data['value'])
                        ? $query->where(fn (Builder $q) => $q
                            ->whereNull('reportable_types')
                            ->orWhereJsonLength('reportable_types', 0)
                            ->orWhereJsonContains('reportable_types', $data['value']))
                        : $query),
                TrashedFilter::make(),
            ])
            ->filtersFormColumns(2)
            ->deferFilters(false)
            ->recordActions([
                ResourceLinkAction::make('viewParentCategory')
                    ->label('Parent')
                    ->openUrlInNewTab()
                    ->relationship('parentCategory'),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
