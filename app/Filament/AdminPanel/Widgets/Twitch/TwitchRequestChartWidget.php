<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets\Twitch;

use App\Services\Twitch\TwitchTracker;
use Filament\Widgets\ChartWidget;

class TwitchRequestChartWidget extends ChartWidget
{
    protected static ?int $sort = 2020;

    protected ?string $heading = 'Requests per Minute (last hour)';

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
        $counts = TwitchTracker::getWindowCounts();

        return [
            'datasets' => [
                [
                    'label' => 'Requests',
                    'data' => array_values($counts),
                    'borderColor' => 'rgba(100, 100, 240)',
                    'backgroundColor' => 'rgba(100, 100, 240, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => array_map(
                static fn (string $bucket): string => mb_substr($bucket, -5),
                array_keys($counts)
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
