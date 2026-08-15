<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets;

use App\Enums\Filament\LucideIcon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Queue;

class DashboardSystemOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'System';

    public static function canView(): bool
    {
        return auth()->user()->getRole()?->id === 0;
    }

    protected function getStats(): array
    {
        $currentDefaultJobs = Queue::size('default');
        $currentModerationJobs = Queue::size('moderation');
        $currentDiscordWebhooksJobs = Queue::size('discord-webhooks');

        $failedJobs = App::make('queue.failer')->count();
        $currentTotalJobs = $currentDefaultJobs + $currentModerationJobs + $currentDiscordWebhooksJobs;

        return [
            Stat::make('Queue Jobs', number_format($currentTotalJobs))
                ->description('Current Jobs from all Queues')
                ->icon(LucideIcon::Server),

            Stat::make('Failed Queue Jobs', number_format($failedJobs))
                ->icon($failedJobs > 0 ? LucideIcon::CircleX : LucideIcon::CircleCheck)
                ->color($failedJobs > 0 ? 'danger' : 'success'),

            Stat::make('Queue "default" Jobs', number_format($currentDefaultJobs))
                ->description('Current Jobs from default Queue')
                ->icon(LucideIcon::Server),

            Stat::make('Queue "moderation" Jobs', number_format($currentModerationJobs))
                ->description('Current Jobs from moderation Queue')
                ->icon(LucideIcon::Server),

            Stat::make('Queue "discord-webhooks" Jobs', number_format($currentDiscordWebhooksJobs))
                ->description('Current Jobs from discord-webhooks Queue')
                ->icon(LucideIcon::Server),
        ];
    }
}
