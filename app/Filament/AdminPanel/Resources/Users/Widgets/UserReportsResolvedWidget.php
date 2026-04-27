<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\Widgets;

use App\Enums\Permission;
use App\Filament\AdminPanel\Widgets\Traits\HasBasicOverviewChartStuff;
use App\Models\Report;
use App\Models\User;
use Carbon\CarbonInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserReportsResolvedWidget extends ChartWidget
{
    use HasBasicOverviewChartStuff;

    public ?User $record = null;

    public static function canView(): bool
    {
        return auth()->user()->can(Permission::ViewAnyReport);
    }

    public function getHeading(): ?string
    {
        $total = Report::query()
            ->withTrashed()
            ->where('reports.resolved_by', $this->record->getKey())
            ->count();

        return "$total Reports Resolved";
    }

    protected function getData(): array
    {
        [$start, $interval, $labelFn] = $this->getCurrentFilter('reports');

        $end = $this->filter === 'day' ? now()->endOfHour() : now()->endOfDay();
        $results = $this->executeQuery('reports', $start, $end, $interval);

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

    protected function getBaseQuery(string $table, CarbonInterface $start, CarbonInterface $end, string $interval): Collection
    {
        $truncUnit = match ($interval) {
            '1 month' => 'month',
            '1 hour' => 'hour',
            default => 'day',
        };

        $submitterId = $this->record->getKey();

        return DB::table(DB::raw(
            "generate_series(
            '{$start->toDateTimeString()}'::timestamp,
            '{$end->toDateTimeString()}'::timestamp,
            '{$interval}'::interval
        ) AS series_date"
        ))
            ->leftJoin($table, function ($join) use ($truncUnit, $table, $submitterId): void {
                $join->on(
                    DB::raw("date_trunc('$truncUnit', $table.resolved_at)"),
                    '=',
                    DB::raw("date_trunc('$truncUnit', series_date)")
                )->where("$table.resolved_by", $submitterId);
            })
            ->selectRaw("date_trunc('$truncUnit', series_date) as date, count($table.id) as aggregate")
            ->groupBy(DB::raw("date_trunc('$truncUnit', series_date)"))
            ->orderBy('date')
            ->get();
    }

    protected function executeQuery(string $table, CarbonInterface $start, CarbonInterface $end, string $interval): Collection
    {
        $filter = $this->filter ?? 'day';
        $ttl = $filter === 'day' ? now()->addMinute() : now()->addHour();
        $cacheKey = 'AdminPanel:charts:'.static::class.":$table:$filter:user:{$this->record->getKey()}";

        return Cache::remember($cacheKey, $ttl, fn (): Collection => $this->getBaseQuery($table, $start, $end, $interval));
    }
}
