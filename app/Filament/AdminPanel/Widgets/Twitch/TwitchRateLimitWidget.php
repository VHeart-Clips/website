<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets\Twitch;

use App\Enums\Filament\LucideIcon;
use App\Services\Twitch\TwitchTracker;
use Filament\Widgets\StatsOverviewWidget;

class TwitchRateLimitWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2010;

    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    protected function getStats(): array
    {
        $rateLimit = TwitchTracker::getRateLimit();
        $hourly = TwitchTracker::getHourlyTotal();
        $averagePerMinute = round($hourly / 60, 1);
        $statuses = TwitchTracker::getStatusCounts();

        $remaining = $rateLimit['remaining'] ?? null;
        $limit = $rateLimit['limit'] ?? null;
        $resetAt = $rateLimit['reset_at'] ? now()->setTimestamp($rateLimit['reset_at'])->diffForHumans() : '-';
        $errors = ($statuses[401] ?? 0) + ($statuses[429] ?? 0) + ($statuses[500] ?? 0);

        return [
            StatsOverviewWidget\Stat::make('Rate Limit Remaining', $remaining !== null ? "$remaining / $limit" : '-')
                ->description($resetAt)
                ->descriptionIcon(LucideIcon::RefreshCcw)
                ->color(match (true) {
                    $remaining === null => 'gray',
                    $remaining < 100 => 'danger',
                    $remaining < 250 => 'warning',
                    default => 'success',
                }),

            StatsOverviewWidget\Stat::make('Requests (last hour)', number_format($hourly))
                ->descriptionIcon(LucideIcon::CircleSlash2)
                ->description("$averagePerMinute requests per minute"),

            StatsOverviewWidget\Stat::make('Errors (401/429 etc)', (string) $errors)
                ->description(collect([200, 401, 429, 500])
                    ->reject(fn ($code) => ($statuses[$code] ?? 0) === 0)
                    ->map(fn ($code) => "$code: ".($statuses[$code] ?? 0))
                    ->implode('  '))
                ->descriptionIcon($errors > 0 ? LucideIcon::TriangleAlert : LucideIcon::CircleCheck)
                ->color($errors > 0 ? 'danger' : 'success'),
        ];
    }
}
