<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Tags;

use App\Enums\Filament\LucideIcon;
use App\Enums\NavigationGroup;
use App\Filament\AdminPanel\Resources\Tags\Pages\ListTags;
use App\Filament\AdminPanel\Resources\Tags\Schemas\TagForm;
use App\Filament\AdminPanel\Resources\Tags\Tables\TagsTable;
use App\Filament\AdminPanel\SharedRelationManagers\AuditsRelationManager;
use App\Models\Clip\Tag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use UnitEnum;

class TagResource extends Resource
{
    use Translatable;

    protected static ?string $model = Tag::class;

    protected static ?int $navigationSort = 200;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Management;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Tag;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AuditsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
        ];
    }
}
