<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardSystemOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'System';

    public static function canView(): bool
    {
        return auth()->user()->getRole()?->id === 0;
    }

    protected function getStats(): array
    {
        $failedJobs = DB::table('failed_jobs')->count();
        $currentJobs = DB::table('jobs')->count();

        return [
            Stat::make('Current Queue Jobs', number_format($currentJobs))
                ->icon(Heroicon::Server),

            Stat::make('Failed Queue Jobs', number_format($failedJobs))
                ->icon($failedJobs > 0 ? Heroicon::XCircle : Heroicon::CheckCircle)
                ->color($failedJobs > 0 ? 'danger' : 'success'),
        ];
    }
}
