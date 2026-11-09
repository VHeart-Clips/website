<?php

declare(strict_types=1);

namespace App\Jobs\Discord\Traits;

use App\Jobs\Discord\BaseDiscordWebhookJob;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JustinKluever\DiscordWebhookBuilder\Webhook;
use RuntimeException;

/**
 * Boilerplate stuff for deleting discord messages via webhooks
 *
 * @phpstan-require-extends BaseDiscordWebhookJob
 *
 * @phpstan-require-implements ShouldBeUnique
 */
trait DeletesDiscordWebhookMessage
{
    public function uniqueId(): string
    {
        return $this->cacheKey('delete');
    }

    protected function getRequest(): PendingRequest|Response
    {
        if ($this->getWebhookMessageId() === null) {
            throw new RuntimeException(self::class.' is missing the message id that should be removed.');
        }

        return Http::timeout(5)->delete($this->getWebhook(), $this->resolvePayload());
    }

    protected function getPayload(): Webhook
    {
        return Webhook::make();
    }
}
