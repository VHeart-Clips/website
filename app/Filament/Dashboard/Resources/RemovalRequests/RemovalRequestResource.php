<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\RemovalRequests;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Broadcaster\DashboardNavigationItem;
use App\Enums\FeatureFlag;
use App\Enums\Filament\LucideIcon;
use App\Filament\Dashboard\Resources\RemovalRequests\Pages\ListRemovalRequests;
use App\Filament\Dashboard\Resources\RemovalRequests\Schemas\RemovalRequestForm;
use App\Filament\Dashboard\Resources\RemovalRequests\Schemas\RemovalRequestInfolist;
use App\Filament\Dashboard\Resources\RemovalRequests\Tables\RemovalRequestsTable;
use App\Models\Broadcaster\RemovalRequest;
use App\Support\FeatureFlag\Feature;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class RemovalRequestResource extends Resource
{
    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $model = RemovalRequest::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Trash2;

    public static function getNavigationLabel(): string
    {
        return DashboardNavigationItem::RemovalRequests->getLabel();
    }

    public static function getLabel(): ?string
    {
        return __('dashboard/resources/removal-requests.resource.label');
    }

    public static function getPluralLabel(): ?string
    {
        return __('dashboard/resources/removal-requests.resource.label_plural');
    }

    public static function canAccess(): bool
    {
        return Feature::isActive(FeatureFlag::BroadcasterRemovalRequestsDashboard) && Gate::allows('dashboardAccess', [Filament::getTenant(), BroadcasterPermission::RemovalRequests]);
    }

    public static function form(Schema $schema): Schema
    {
        return RemovalRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RemovalRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RemovalRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRemovalRequests::route('/'),
        ];
    }
}
