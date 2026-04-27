<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets;

use App\Enums\Permission;
use App\Filament\AdminPanel\Widgets\Traits\HasBasicOverviewChartStuff;
use Filament\Widgets\ChartWidget;

class VotesChartWidget extends ChartWidget
{
    use HasBasicOverviewChartStuff;

    protected ?string $heading = 'Votes Trend Overview';

    public static function canView(): bool
    {
        return auth()->user()->can(Permission::ViewAnyUser);
    }

    protected function getFilters(): ?array
    {
        return [
            'day' => 'Last 24 Hours',
            'week' => 'Last 7 days',
            'month' => 'Last 30 days',
        ];
    }

    protected function getData(): array
    {
        [$start, $interval, $labelFn] = $this->getCurrentFilter();

        $end = $this->filter === 'day' ? now()->endOfHour() : now()->endOfDay();
        $results = $this->executeQuery('votes', $start, $end, $interval);

        return [
            'datasets' => [[
                'data' => $results->pluck('aggregate')->toArray(),
                'borderColor' => 'rgb(100, 100, 240)',
                'backgroundColor' => 'rgba(100, 100, 240, 0.08)',
                'borderWidth' => 2,
                'pointRadius' => 0,
                'pointHoverRadius' => 4,
                'fill' => true,
                'tension' => 0.4,
            ]],
            'labels' => $results->map(fn ($v) => $labelFn($v->date))->toArray(),
        ];
    }
}
