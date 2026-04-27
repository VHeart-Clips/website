<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets\Traits;

use Carbon\CarbonInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @mixin ChartWidget
 */
trait HasBasicOverviewChartStuff
{
    public ?string $filter = 'day';

    protected int|string|array $columnSpan = 2;

    protected static ?int $sort = 999;

    public function getMaxHeight(): ?string
    {
        return '200px';
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

    protected function executeQuery(string $table, CarbonInterface $start, CarbonInterface $end, string $interval)
    {
        $filter = $this->filter ?? 'day';
        $ttl = $filter === 'day' ? now()->addMinute() : now()->addHour();
        $cacheKey = 'AdminPanel:charts:'.static::class.":$table:$filter";

        return Cache::remember($cacheKey, $ttl, fn () => $this->getBaseQuery($table, $start, $end, $interval));
    }

    protected function getBaseQuery(string $table, CarbonInterface $start, CarbonInterface $end, string $interval): Collection
    {
        $truncUnit = match ($interval) {
            '1 month' => 'month',
            '1 hour' => 'hour',
            default => 'day',
        };

        // laravel trend has an issue with postgres, thats why i do it myself
        return DB::table(DB::raw('
                    generate_series(
                        ?::timestamp,
                        ?::timestamp,
                        ?::interval
                    ) AS series_date
                '))
            ->setBindings([
                $start->toDateTimeString(),
                $end->toDateTimeString(),
                $interval,
            ])
            ->leftJoin($table, function ($join) use ($truncUnit, $table): void {
                $join->on(
                    DB::raw("date_trunc('$truncUnit', $table.created_at)"),
                    '=',
                    DB::raw("date_trunc('$truncUnit', series_date)")
                );
            })
            ->selectRaw("date_trunc('$truncUnit', series_date) as date, count($table.id) as aggregate")
            ->groupBy(DB::raw("date_trunc('$truncUnit', series_date)"))
            ->orderBy('date')
            ->get();
    }

    protected function getAllTimeStart(?string $table): CarbonInterface
    {
        if ($firstRecord = DB::table($table)->min('created_at')) {
            return Carbon::parse($firstRecord)->startOfYear();
        }

        return now()->startOfYear();
    }

    protected function getCurrentFilter(?string $table = null): array
    {
        return match ($this->filter ?? 'day') {
            'day' => [
                now()->subDay()->startOfHour(),
                '1 hour',
                fn (string $d): string => Carbon::parse($d)->format('H:i'),
            ],
            'month' => [
                now()->subDays(29)->startOfDay(),
                '1 day',
                fn (string $d): string => Carbon::parse($d)->format('d M'),
            ],
            'year' => [
                now()->subMonths(11)->startOfMonth(),
                '1 month',
                fn (string $d): string => Carbon::parse($d)->format('M Y'),
            ],
            'all' => [
                $this->getAllTimeStart($table),
                '1 month',
                fn (string $d): string => Carbon::parse($d)->format('M Y'),
            ],
            default => [
                now()->subDays(6)->startOfDay(),
                '1 day',
                fn (string $d): string => Carbon::parse($d)->format('D'),
            ],
        };
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
