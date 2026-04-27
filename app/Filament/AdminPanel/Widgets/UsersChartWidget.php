<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets;

use App\Enums\Permission;
use App\Filament\AdminPanel\Widgets\Traits\HasBasicOverviewChartStuff;
use Filament\Widgets\ChartWidget;

class UsersChartWidget extends ChartWidget
{
    use HasBasicOverviewChartStuff;

    protected ?string $heading = 'Users Trend Overview';

    public static function canView(): bool
    {
        return auth()->user()->can(Permission::ViewAnyUser);
    }

    protected function getData(): array
    {
        [$start, $interval, $labelFn] = $this->getCurrentFilter('votes');

        $end = $this->filter === 'day' ? now()->endOfHour() : now()->endOfDay();
        $results = $this->executeQuery('users', $start, $end, $interval);

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
