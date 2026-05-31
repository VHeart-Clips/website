<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\RemovalRequests;

use App\Enums\Filament\LucideIcon;
use App\Enums\NavigationGroup;
use App\Filament\AdminPanel\Resources\RemovalRequests\Pages\ListRemovalRequests;
use App\Filament\AdminPanel\Resources\RemovalRequests\Pages\ViewRemovalRequest;
use App\Filament\AdminPanel\Resources\RemovalRequests\RelationManagers\RemovalRequestCompilationsRelationManager;
use App\Filament\AdminPanel\Resources\RemovalRequests\Schemas\RemovalRequestInfolist;
use App\Filament\AdminPanel\Resources\RemovalRequests\Tables\RemovalRequestsTable;
use App\Models\Broadcaster\RemovalRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class RemovalRequestResource extends Resource
{
    protected static ?string $model = RemovalRequest::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Trash2;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Moderation;

    public static function getLabel(): ?string
    {
        return __('admin/resources/removal-requests.resource.label');
    }

    public static function getPluralLabel(): ?string
    {
        return __('admin/resources/removal-requests.resource.label_plural');
    }

    public static function infolist(Schema $schema): Schema
    {
        return RemovalRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RemovalRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RemovalRequestCompilationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRemovalRequests::route('/'),
            'view' => ViewRemovalRequest::route('/{record}'),
        ];
    }
}
