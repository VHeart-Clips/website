<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Categories;

use App\Enums\Filament\LucideIcon;
use App\Enums\NavigationGroup;
use App\Filament\AdminPanel\Resources\Categories\Pages\ListCategories;
use App\Filament\AdminPanel\Resources\Categories\Schemas\CategoryForm;
use App\Filament\AdminPanel\Resources\Categories\Tables\CategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Management;

    protected static ?int $navigationSort = 100;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Folder;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            // 'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
