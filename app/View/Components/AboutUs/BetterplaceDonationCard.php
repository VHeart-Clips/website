<?php

declare(strict_types=1);

namespace App\View\Components\AboutUs;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Number;
use Illuminate\View\Component;
use RuntimeException;

class BetterplaceDonationCard extends Component
{
    private const string BETTERPLACE_BASE = 'https://api.betterplace.org/de/api_v4/fundraising_events/';

    private const int HTTP_TIMEOUT = 2;

    // After how many seconds do we consider the data stale (and refresh in the background)
    private const int CACHE_TTL_STALE = 300;

    // After how many seconds do we want to force a refresh
    private const int CACHE_TTL_INVALID = 86_400;

    public function __construct(
        public readonly int $eventId = 55712,
    ) {}

    public function render(): View
    {
        [$event, $donations, $error] = $this->loadData();

        return view('components.about-us.betterplace-donation-card', [
            'projectTitle' => $event['title'] ?? __('betterplace.title'),
            'projectDescription' => $event['description'],
            'projectAmount' => Number::currency($event['amount'], 'EUR', app()->getLocale(), 2),
            'donations' => $donations,
            'error' => $error,
        ]);
    }

    private function loadData(): array
    {
        try {
            return Cache::flexible(
                "betterplace_donation_card_{$this->eventId}",
                [self::CACHE_TTL_STALE, self::CACHE_TTL_INVALID],
                function (): array {
                    [$eventResponse, $donationsResponse] = Http::timeout(self::HTTP_TIMEOUT)
                        ->pool(fn (Pool $pool): array => [
                            $pool->get(self::BETTERPLACE_BASE.$this->eventId.'.json'),
                            $pool->get(self::BETTERPLACE_BASE.$this->eventId.'/opinions.json'),
                        ]);

                    if (! $eventResponse->successful() || ! $donationsResponse->successful()) {
                        throw new RuntimeException('Betterplace API request failed.');
                    }

                    $event = [
                        'title' => $eventResponse->json()['title'] ?? null,
                        'description' => $eventResponse->json()['description'] ?? null,
                        'amount' => ($eventResponse->json()['donated_amount_in_cents'] ?? 0) / 100,
                    ];

                    $donations = collect($donationsResponse->json()['data'] ?? [])
                        ->map(fn (array $donation): array => [
                            'name' => $donation['author']['name'] ?? $donation['donator_name'] ?? __('betterplace.anonymous'),
                            'image' => $donation['author']['picture']['links'][0]['href'] ?? $donation['donator_picture'] ?? null,
                            'message' => $donation['message'] ?? null,
                            'amount' => ($donation['donated_amount_in_cents'] ?? $donation['amount_in_cents'] ?? 0) / 100,
                        ])
                        ->all();

                    return [$event, $donations, null];
                }
            );
        } catch (Exception $exception) {
            report($exception);

            return [null, null, __('betterplace.error')];
        }
    }
}
