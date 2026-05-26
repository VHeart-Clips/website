<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Widgets;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Gate;

class ClipsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Clips';

    public static function canView(): bool
    {
        return Gate::allows('dashboardAccess', [Filament::getTenant(), BroadcasterPermission::Clips]);
    }

    protected function getStats(): array
    {
        $tenantId = Filament::getTenant()->id;
        $clipBaseQuery = Clip::whereBroadcasterId($tenantId)->withoutGlobalScope(filament()->getTenancyScopeName());

        $totalClips = $clipBaseQuery->clone()->count();
        $totalClipsToday = $clipBaseQuery->clone()->where('created_at', '>=', now()->startOfDay())->count();
        $totalClipsThisWeek = $clipBaseQuery->clone()->where('created_at', '>=', now()->startOfWeek())->count();
        $totalClipsThisMonth = $clipBaseQuery->clone()->where('created_at', '>=', now()->startOfMonth())->count();

        $averageDuration = $clipBaseQuery->clone()->avg('duration') ?? 0;
        $clipsLast30Days = $clipBaseQuery->clone()->where('created_at', '>=', now()->subDays(30))->count();
        $averageClipsPerDay = $clipsLast30Days / 30;

        return [
            Stat::make('Total Clips Submitted', number_format($totalClips))
                ->icon(LucideIcon::Video),
            Stat::make('Clips Submitted Today', number_format($totalClipsToday))
                ->icon(LucideIcon::Video),
            Stat::make('Clips Submitted This Week', number_format($totalClipsThisWeek))
                ->icon(LucideIcon::Video),
            Stat::make('Clips Submitted This Month', number_format($totalClipsThisMonth))
                ->icon(LucideIcon::Video),

            Stat::make('Avg. Clip Duration', number_format($averageDuration).'s')
                ->icon(LucideIcon::Clock),
            Stat::make('Avg. Daily Submissions (30 Days)', number_format($averageClipsPerDay, 2))
                ->icon(LucideIcon::ChartBar),
        ];
    }
}
