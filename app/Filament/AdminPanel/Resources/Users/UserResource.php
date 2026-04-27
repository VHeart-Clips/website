<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users;

use App\Enums\Filament\LucideIcon;
use App\Enums\NavigationGroup;
use App\Filament\AdminPanel\Resources\Users\Pages\EditUser;
use App\Filament\AdminPanel\Resources\Users\Pages\ListUsers;
use App\Filament\AdminPanel\Resources\Users\Pages\ViewUser;
use App\Filament\AdminPanel\Resources\Users\RelationManagers\BroadcastedClipsRelationManager;
use App\Filament\AdminPanel\Resources\Users\RelationManagers\CreatedClipsRelationManager;
use App\Filament\AdminPanel\Resources\Users\RelationManagers\SubmittedClipsRelationManager;
use App\Filament\AdminPanel\Resources\Users\Schemas\UserForm;
use App\Filament\AdminPanel\Resources\Users\Schemas\UserInfolist;
use App\Filament\AdminPanel\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Administration;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Users;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BroadcastedClipsRelationManager::make(),
            CreatedClipsRelationManager::make(),
            SubmittedClipsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            // 'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
