<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets\Twitch;

use App\Services\Twitch\TwitchTracker;
use Filament\Widgets\ChartWidget;

class TwitchEndpointChartWidget extends ChartWidget
{
    protected static ?int $sort = 2030;

    protected ?string $heading = 'Top Endpoints (last hour)';

    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    public function getMaxHeight(): ?string
    {
        return '200px';
    }

    protected function getData(): array
    {
        $endpoints = array_slice(TwitchTracker::getEndpointCounts(), 0, 10, true);

        return [
            'datasets' => [
                [
                    'label' => 'Requests',
                    'data' => array_values($endpoints),
                    'backgroundColor' => 'rgba(100, 100, 240)',
                ],
            ],
            'labels' => array_keys($endpoints),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
