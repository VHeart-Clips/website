<?php

declare(strict_types=1);

namespace App\Services\Twitch;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class TwitchTracker
{
    private const string RATE_LIMIT_KEY = 'twitch:rate-limit';

    private const string WINDOW_PREFIX = 'twitch:window:';

    private const string STATUS_KEY = 'twitch:stats:status';

    public static function getRateLimit(): array
    {
        return Cache::get(self::RATE_LIMIT_KEY, [
            'limit' => null,
            'remaining' => null,
            'reset_at' => null,
            'updated_at' => null,
        ]);
    }

    public static function getWindowCounts(int $minutes = 60): array
    {
        $counts = [];
        $now = now();

        for ($i = $minutes - 1; $i >= 0; $i--) {
            $bucket = $now->copy()->subMinutes($i)->format('Y-m-d-H-i');
            $counts[$bucket] = (int) Redis::get(self::WINDOW_PREFIX.$bucket);
        }

        return $counts;
    }

    public static function getHourlyTotal(): int
    {
        return array_sum(self::getWindowCounts(60));
    }

    public static function getStatusCounts(): array
    {
        return Cache::get(self::STATUS_KEY, []);
    }

    public static function reset(): void
    {
        Cache::forget(self::RATE_LIMIT_KEY);
        Cache::forget(self::STATUS_KEY);
    }

    public static function getEndpointCounts(int $minutes = 60): array
    {
        $now = now();
        $counts = [];
        $prefix = config('database.redis.options.prefix', '');

        for ($i = $minutes - 1; $i >= 0; $i--) {
            $bucket = $now->copy()->subMinutes($i)->format('Y-m-d-H-i');
            $keys = Redis::keys(self::WINDOW_PREFIX."endpoint:*:$bucket");

            foreach ($keys as $key) {
                $unprefixed = str_starts_with((string) $key, (string) $prefix) ? mb_substr((string) $key, mb_strlen((string) $prefix)) : $key;
                $label = preg_replace('/^twitch:window:endpoint:(.+):\d{4}-\d{2}-\d{2}-\d{2}-\d{2}$/', '$1', (string) $unprefixed);
                $counts[$label] = ($counts[$label] ?? 0) + (int) Redis::get($unprefixed);
            }
        }

        arsort($counts);

        return $counts;
    }

    public static function record(Response $response, string $endpoint, string $method): void
    {
        self::updateRateLimit($response);
        self::incrementWindow($method, $endpoint);
        self::incrementStatus($response->status());
    }

    private static function updateRateLimit(Response $response): void
    {
        $limit = $response->header('Ratelimit-Limit');
        $remaining = $response->header('Ratelimit-Remaining');
        $reset = $response->header('Ratelimit-Reset');

        if (! $limit && ! $remaining) {
            return;
        }

        Cache::put(self::RATE_LIMIT_KEY, [
            'limit' => (int) $limit,
            'remaining' => (int) $remaining,
            'reset_at' => $reset ? (int) $reset : null,
            'updated_at' => now()->toIso8601String(),
        ], now()->addHour());
    }

    private static function incrementStatus(int $status): void
    {
        $stats = self::getStatusCounts();
        $stats[$status] = ($stats[$status] ?? 0) + 1;
        Cache::put(self::STATUS_KEY, $stats, now()->addDay());
    }

    private static function incrementWindow(string $method, string $endpoint): void
    {
        $bucket = self::WINDOW_PREFIX.now()->format('Y-m-d-H-i');
        Redis::incr($bucket);
        Redis::expire($bucket, 3660);

        $endpointKey = self::WINDOW_PREFIX."endpoint:$method:$endpoint:".now()->format('Y-m-d-H-i');
        Redis::incr($endpointKey);
        Redis::expire($endpointKey, 3660);
    }
}
