<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Roles;

use App\Enums\Filament\LucideIcon;
use App\Enums\NavigationGroup;
use App\Filament\AdminPanel\Resources\Roles\Pages\CreateRole;
use App\Filament\AdminPanel\Resources\Roles\Pages\EditRole;
use App\Filament\AdminPanel\Resources\Roles\Pages\ListRoles;
use App\Filament\AdminPanel\Resources\Roles\Pages\ViewRole;
use App\Filament\AdminPanel\Resources\Roles\Schemas\RoleForm;
use App\Filament\AdminPanel\Resources\Roles\Schemas\RoleInfolist;
use App\Filament\AdminPanel\Resources\Roles\Tables\RolesTable;
use App\Filament\AdminPanel\SharedRelationManagers\AuditsRelationManager;
use App\Models\Role;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use UnitEnum;

class RoleResource extends Resource
{
    use Translatable;

    protected static ?string $model = Role::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Administration;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Award;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
