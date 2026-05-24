<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters;

use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Broadcasters\Pages\EditBroadcaster;
use App\Filament\AdminPanel\Resources\Broadcasters\Pages\ListBroadcasters;
use App\Filament\AdminPanel\Resources\Broadcasters\Pages\ViewBroadcaster;
use App\Filament\AdminPanel\Resources\Broadcasters\RelationManagers\CategoryFiltersRelationManager;
use App\Filament\AdminPanel\Resources\Broadcasters\RelationManagers\ConsentLogsRelationManager;
use App\Filament\AdminPanel\Resources\Broadcasters\RelationManagers\MembersRelationManager;
use App\Filament\AdminPanel\Resources\Broadcasters\RelationManagers\UserFiltersRelationManager;
use App\Filament\AdminPanel\Resources\Broadcasters\Schemas\BroadcasterForm;
use App\Filament\AdminPanel\Resources\Broadcasters\Schemas\BroadcasterInfolist;
use App\Filament\AdminPanel\Resources\Broadcasters\Tables\BroadcastersTable;
use App\Models\Broadcaster\Broadcaster;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BroadcasterResource extends Resource
{
    protected static ?string $model = Broadcaster::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::UserStar;

    protected static ?int $navigationSort = 100;

    protected static ?string $recordTitleAttribute = 'user.name';

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record->user->name;
    }

    public static function form(Schema $schema): Schema
    {
        return BroadcasterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BroadcasterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BroadcastersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UserFiltersRelationManager::make(),
            CategoryFiltersRelationManager::make(),
            ConsentLogsRelationManager::make(),
            MembersRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBroadcasters::route('/'),
            'view' => ViewBroadcaster::route('/{record}'),
            'edit' => EditBroadcaster::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withTrashed()
            ->with(['user', 'latestConsentLog']);
    }
}
