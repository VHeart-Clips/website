<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
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
        $totalBroadcasters = User::where('clip_permission', true)->count();

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->icon(Heroicon::Users)
                ->color('primary'),

            Stat::make('Total Broadcasters', number_format($totalBroadcasters))
                ->icon(Heroicon::Users)
                ->color('primary'),
        ];
    }
}
