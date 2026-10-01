<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\Widgets;

use App\Models\Vote;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Model;

class UserVotesWidget extends ChartWidget
{
    public ?Model $record = null;

    protected ?string $maxHeight = '200px';

    protected int|string|array $columnSpan = 2;

    public function getHeading(): ?string
    {
        $total = Vote::query()
            ->where('user_id', $this->record->getKey())
            ->count();

        return "$total Votes";
    }

    protected function getFilters(): ?array
    {
        return [
            'day' => 'Last 24 Hours',
            'week' => 'Last 7 days',
            'month' => 'Last 30 days',
            'year' => 'Last year',
            'all' => 'All time',
        ];
    }

    protected function getCurrentFilter(): array
    {
        return match ($this->filter ?? 'day') {
            'day' => [
                now()->subDay()->startOfHour(),
                'perHour',
                fn (string $d): string => Carbon::parse($d)->format('H:i'),
            ],
            'month' => [
                now()->subDays(29)->startOfDay(),
                'perDay',
                fn (string $d): string => Carbon::parse($d)->format('d M'),
            ],
            'year' => [
                now()->subMonths(11)->startOfMonth(),
                'perMonth',
                fn (string $d): string => Carbon::parse($d)->format('M Y'),
            ],
            'all' => [
                Carbon::parse('01.04.2026'), // we just hardcode the release month for simplicity haha
                'perMonth',
                fn (string $d): string => Carbon::parse($d)->format('M Y'),
            ],
            default => [
                now()->subDays(6)->startOfDay(),
                'perDay',
                fn (string $d): string => Carbon::parse($d)->format('D'),
            ],
        };
    }

    protected function getData(): array
    {
        [$start, $perPeriod, $labelFn] = $this->getCurrentFilter();

        $trend = Trend::query(Vote::query()->where('user_id', $this->record->getKey()))
            ->between(start: $start, end: now()->endOfDay())
            ->{$perPeriod}()
            ->count();

        return [
            'datasets' => [[
                'data' => $trend->map(fn (TrendValue $v): mixed => $v->aggregate)->toArray(),
                'borderColor' => 'rgb(100, 100, 240)',
                'backgroundColor' => 'rgba(100, 100, 240, 0.08)',
                'borderWidth' => 2,
                'pointRadius' => 0,
                'pointHoverRadius' => 4,
                'fill' => true,
                'tension' => 0.4,
            ]],
            'labels' => $trend->map(fn (TrendValue $v): string => $labelFn($v->date))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'maxTicksLimit' => 7,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                        'maxTicksLimit' => 4,
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
