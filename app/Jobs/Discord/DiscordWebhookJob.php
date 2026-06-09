<?php

declare(strict_types=1);

namespace App\Jobs\Discord;

use App\Events\Discord\DiscordWebhookDied;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\Skip;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Spatie\DiscordAlerts\Jobs\SendToDiscordChannelJob;

#[Tries(254)]
#[MaxExceptions(3)]
class DiscordWebhookJob extends SendToDiscordChannelJob implements ShouldBeEncrypted
{
    private readonly string $webhookId;

    public function __construct(
        string $text,
        string $webhookUrl,
        ?string $username = null,
        bool $tts = false,
        ?string $avatar_url = null,
        ?array $embeds = null
    ) {
        if (! preg_match('/webhooks\/(\d+)\//', $webhookUrl, $match)) {
            throw new InvalidArgumentException('Could not extract Webhook Id from provided webhook url, are you sure this is a discord webhook url?');
        }

        parent::__construct($text, $webhookUrl, $username, $tts, $avatar_url, $embeds);
        $this->webhookId = $match[1];
    }

    public function middleware(): array
    {
        return [
            Skip::when(fn (): bool => $this->isWebhookInvalid()),
            new RateLimited('discord-webhooks'),
            new WithoutOverlapping($this->cacheKey('lock'), 1, 10),
        ];
    }

    public function handle(): void
    {
        if ($this->releaseIfCurrentlyRateLimited()) {
            return;
        }

        try {
            $response = Http::timeout(5)->post($this->webhookUrl, $this->getPayload());
        } catch (ConnectionException) {
            $this->release(30);

            return;
        }

        if ($response->serverError()) {
            $this->release($this->attempts() * 3);

            return;
        }

        if ($this->failIfWebhookNotFound($response)) {
            return;
        }

        $this->updateRateLimitStatus($response);

        if ($this->releaseIfRateLimitedAfter($response)) {
            return;
        }

        if ($response->failed()) {
            $this->fail("Discord webhook $this->webhookId failed with status {$response->status()}: {$response->body()}");
        }
    }

    private function isWebhookInvalid(): bool
    {
        return Cache::has($this->cacheKey('invalid'));
    }

    private function updateRateLimitStatus(Response $response): void
    {
        $rateLimitRemainingHeader = $response->header('X-RateLimit-Remaining');
        $rateLimitResetAtHeader = $response->header('X-RateLimit-Reset');

        if ($rateLimitRemainingHeader !== null) {
            $ttl = (int) ($response->header('X-RateLimit-Reset-After') ?? 60) + 5;
            Cache::put($this->cacheKey('rate-limit:remaining'), (int) $rateLimitRemainingHeader, $ttl);
        }

        if ($rateLimitResetAtHeader !== null) {
            Cache::put($this->cacheKey('rate-limit:reset'), (int) $rateLimitResetAtHeader, 120);
        }
    }

    private function getPayload(): array
    {
        $payload = [
            'content' => $this->text,
            'tts' => $this->tts,
        ];

        if (filled($this->username)) {
            $payload['username'] = $this->username;
        }

        if (filled($this->avatar_url)) {
            $payload['avatar_url'] = $this->avatar_url;
        }

        if (filled($this->embeds)) {
            $payload['embeds'] = $this->embeds;
        }

        return $payload;
    }

    private function cacheKey(string $suffix): string
    {
        return "discord:webhook:$this->webhookId:$suffix";
    }

    private function releaseIfRateLimitedAfter(Response $response): bool
    {
        if ($response->status() !== 429) {
            return false;
        }

        $body = $response->json() ?? [];
        $retryAfter = (int) ceil($body['retry_after'] ?? $response->header('Retry-After') ?? 60);
        $scope = $response->header('X-RateLimit-Scope') ?? 'unknown';

        Log::debug("Webhook '$this->webhookId' has triggered discord rate-limit in the '$scope' rate limit scope", [
            'webhook_id' => $this->webhookId,
            'retry-after' => $retryAfter,
            'scope' => $scope,
        ]);

        $this->release($retryAfter + 5);

        return true;
    }

    private function releaseIfCurrentlyRateLimited(): bool
    {
        $rateLimitRemaining = Cache::get($this->cacheKey('rate-limit:remaining'));
        $rateLimitResetAt = Cache::get($this->cacheKey('rate-limit:reset'));

        if (
            $rateLimitRemaining !== null
            && $rateLimitRemaining <= 1
            && is_int($rateLimitResetAt)
            && $rateLimitResetAt >= now()->timestamp
        ) {
            $retryAfter = max($rateLimitResetAt - now()->timestamp, 1);

            Log::debug('Discord Webhook has been delayed', [
                'webhook_id' => $this->webhookId,
                'retry_after' => $retryAfter,
            ]);

            $this->release($retryAfter + 5);

            return true;
        }

        return false;
    }

    private function failIfWebhookNotFound(Response $response): bool
    {
        if ($response->status() !== 404) {
            return false;
        }

        Log::info("Discord Webhook '$this->webhookId' was not found on discord, discarding future attempts.", [
            'webhook_id' => $this->webhookId,
        ]);

        Cache::put($this->cacheKey('invalid'), true, now()->addWeek());
        DiscordWebhookDied::dispatch($this->webhookId, $this->webhookUrl);

        $this->fail("Webhook '$this->webhookId' was not found (404)");

        return true;
    }
}
