<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets\BetterPlace;

use App\Services\BetterPlace\BetterPlaceService;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class MonthlyDonationChart extends ChartWidget
{
    private const int EVENT_ID = 55712;

    protected static ?int $sort = -200;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected ?string $heading = 'Spenden';

    public function getHeading(): string|Htmlable|null
    {
        return 'Spenden pro Monat';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $months = Cache::flexible(
            'admin.betterplace.monthly.'.self::EVENT_ID,
            [now()->addDay(), now()->addWeek()],
            fn (): array => rescue(fn (): array => $this->getMonthlyDonationData()) ?? []
        );

        return [
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => 'EUR',
                    'data' => array_column($months, 'amount'),
                    'yAxisID' => 'y',
                    'order' => 1,
                ],
                [
                    'type' => 'line',
                    'label' => 'Spenden',
                    'data' => array_column($months, 'count'),
                    'yAxisID' => 'y1',
                    'order' => 0,
                    'tension' => 0.3,
                    'backgroundColor' => 'oklch(58.5% 0.233 277.117)', // indigo-500
                    'borderColor' => 'oklch(58.5% 0.233 277.117)', // indigo-500
                    'pointBackgroundColor' => 'oklch(58.5% 0.233 277.117)', // indigo-500
                ],
            ],
            'labels' => array_map(
                static fn (string $key) => Date::createFromFormat('Y-m', $key)->translatedFormat('M Y'),
                array_keys($months),
            ),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'position' => 'left',
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'position' => 'right',
                    'beginAtZero' => true,
                    'grid' => ['drawOnChartArea' => false],
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    private function getMonthlyDonationData(): array
    {
        $now = Date::now();
        $cutoff = $now->copy()->subMonths(11)->startOfMonth();
        $sums = [];
        $page = 1;

        do {
            $response = BetterPlaceService::getFundraisingOpinions(
                facets: ['has_amount:true'],
                page: $page,
                perPage: 200,
            );

            foreach ($response->opinions as $opinion) {
                $cents = $opinion->donatedAmountInCents;

                if ($cents <= 0 || empty($opinion->confirmedAt)) {
                    continue;
                }

                if ($opinion->confirmedAt->lessThan($cutoff)) {
                    break 2;
                }

                $key = $opinion->confirmedAt->format('Y-m');
                $sums[$key]['cents'] = ($sums[$key]['cents'] ?? 0) + $cents;
                $sums[$key]['count'] = ($sums[$key]['count'] ?? 0) + 1;
            }

            $page++;
        } while ($page < ($response->totalPages));

        if ($sums === []) {
            return [];
        }

        $result = [];

        foreach (CarbonPeriod::create($cutoff, '1 month', $now->copy()->startOfMonth()) as $month) {
            $key = $month->format('Y-m');

            $result[$key] = [
                'amount' => round(($sums[$key]['cents'] ?? 0) / 100, 2),
                'count' => $sums[$key]['count'] ?? 0,
            ];
        }

        return $result;
    }
}
