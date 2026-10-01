<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\ReportCategories;

use App\Enums\Filament\LucideIcon;
use App\Enums\NavigationGroup;
use App\Filament\AdminPanel\Resources\ReportCategories\Pages\CreateReportCategory;
use App\Filament\AdminPanel\Resources\ReportCategories\Pages\EditReportCategory;
use App\Filament\AdminPanel\Resources\ReportCategories\Pages\ListReportCategories;
use App\Filament\AdminPanel\Resources\ReportCategories\Pages\ViewReportCategory;
use App\Filament\AdminPanel\Resources\ReportCategories\RelationManagers\SubCategoriesRelationManager;
use App\Filament\AdminPanel\Resources\ReportCategories\Schemas\ReportCategoryForm;
use App\Filament\AdminPanel\Resources\ReportCategories\Schemas\ReportCategoryInfolist;
use App\Filament\AdminPanel\Resources\ReportCategories\Tables\ReportCategoriesTable;
use App\Filament\AdminPanel\SharedRelationManagers\AuditsRelationManager;
use App\Models\ReportCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use UnitEnum;

class ReportCategoryResource extends Resource
{
    use Translatable;

    protected static ?string $model = ReportCategory::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::List;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Management;

    public static function form(Schema $schema): Schema
    {
        return ReportCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReportCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SubCategoriesRelationManager::make(),
            AuditsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportCategories::route('/'),
            'create' => CreateReportCategory::route('/create'),
            'view' => ViewReportCategory::route('/{record}'),
            'edit' => EditReportCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('parentCategory', fn (BelongsTo $builder) => $builder->withTrashed());
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
