<?php

declare(strict_types=1);

namespace App\Jobs\Discord;

use App\Events\Discord\DiscordWebhookDied;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\Skip;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JustinKluever\DiscordWebhookBuilder\Webhook;

#[Tries(254)]
#[MaxExceptions(3)]
abstract class BaseDiscordWebhookJob implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The Webhook Payload we should send to discord
     */
    abstract protected function getPayload(): Webhook;

    /**
     * The Webhook Url we send the Payload to
     */
    abstract protected function getWebhook(): string;

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
        if ($this->shouldRun() === false) {
            return;
        }

        if ($this->releaseIfCurrentlyRateLimited()) {
            return;
        }

        try {
            Log::debug("Sending to Discord Webhook {$this->getWebhookId()}", [
                'webhook_id' => $this->getWebhookId(),
                'payload' => $this->getPayload(),
            ]);

            $response = $this->getRequest();

            if ($response instanceof PendingRequest) {
                $response = $response->post($this->getWebhook(), $this->getPayload());
            }
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
            $this->fail("Discord webhook {$this->getWebhookId()} failed with status {$response->status()}: {$response->body()}");

            return;
        }

        $this->handleResponse($response);
    }

    /**
     * Allows us to override the entire request if needed
     *
     * @throws ConnectionException
     */
    protected function getRequest(): PendingRequest|Response
    {
        return Http::timeout(5)->post($this->getWebhook(), $this->getPayload());
    }

    /**
     * Allows us to do stuff with the response later
     */
    protected function handleResponse(Response $response): void {}

    /**
     * In case we need a way to easily stop a job at handle time
     */
    protected function shouldRun(): bool
    {
        return true;
    }

    protected function getWebhookId(): string
    {
        if (! preg_match('/webhooks\/(\d+)\//', $this->getWebhook(), $match)) {
            throw new InvalidArgumentException('Could not extract Webhook Id from provided webhook url, are you sure this is a discord webhook url?');
        }

        return $match[1];
    }

    protected function isWebhookInvalid(): bool
    {
        return Cache::has($this->cacheKey('invalid'));
    }

    protected function cacheKey(string $suffix): string
    {
        return "discord:webhook:{$this->getWebhookId()}:$suffix";
    }

    protected function updateRateLimitStatus(Response $response): void
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

    protected function releaseIfRateLimitedAfter(Response $response): bool
    {
        if ($response->status() !== 429) {
            return false;
        }

        $body = $response->json() ?? [];
        $retryAfter = (int) ceil($body['retry_after'] ?? $response->header('Retry-After') ?? 60);
        $scope = $response->header('X-RateLimit-Scope') ?? 'unknown';

        Log::debug("Webhook '{$this->getWebhookId()}' has triggered discord rate-limit in the '$scope' rate limit scope", [
            'webhook_id' => $this->getWebhookId(),
            'retry-after' => $retryAfter,
            'scope' => $scope,
        ]);

        $this->release($retryAfter + 5);

        return true;
    }

    protected function releaseIfCurrentlyRateLimited(): bool
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
                'webhook_id' => $this->getWebhookId(),
                'retry_after' => $retryAfter,
            ]);

            $this->release($retryAfter + 5);

            return true;
        }

        return false;
    }

    protected function failIfWebhookNotFound(Response $response): bool
    {
        if ($response->status() !== 404) {
            return false;
        }

        Log::info("Discord Webhook '{$this->getWebhookId()}' was not found on discord, discarding future attempts.", [
            'webhook_id' => $this->getWebhookId(),
        ]);

        Cache::put($this->cacheKey('invalid'), true, now()->addWeek());
        DiscordWebhookDied::dispatch($this->getWebhookId(), $this->getWebhook());

        $this->fail("Webhook '{$this->getWebhookId()}' was not found (404)");

        return true;
    }
}
