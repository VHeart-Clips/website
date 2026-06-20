<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets;

use App\Enums\Filament\LucideIcon;
use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardUsersOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Users';

    public static function canView(): bool
    {
        return auth()->user()->can(Permission::ViewAnyUser);
    }

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $totalBroadcasters = Broadcaster::query()->whereHasGivenConsent()->count();

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->icon(LucideIcon::Users)
                ->color('primary'),

            Stat::make('Total Broadcasters', number_format($totalBroadcasters))
                ->icon(LucideIcon::Users)
                ->color('primary'),
        ];
    }
}
