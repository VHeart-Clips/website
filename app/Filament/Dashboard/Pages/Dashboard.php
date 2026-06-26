<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Pages;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Filament\LucideIcon;
use App\Filament\Dashboard\Traits\DisabledNavigationUntilOnboarding;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;

class Dashboard extends BaseDashboard
{
    use DisabledNavigationUntilOnboarding;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::House;

    public static function canAccess(): bool
    {
        return array_any(
            BroadcasterPermission::cases(),
            fn (BroadcasterPermission $permission) => Gate::allows('dashboardAccess', [Filament::getTenant(), $permission])
        );
    }

    public static function getNavigationLabel(): string
    {
        return __('dashboard/navigation.dashboard');
    }

    public function getTitle(): string|Htmlable
    {
        return Filament::getTenant()->name.' - '.self::getNavigationLabel();
    }

    public function getHeading(): string|Htmlable|null
    {
        return self::getNavigationLabel();
    }
}
