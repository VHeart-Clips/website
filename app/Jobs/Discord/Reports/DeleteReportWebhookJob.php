<?php

declare(strict_types=1);

namespace App\Jobs\Discord\Reports;

use App\Jobs\Discord\BaseDiscordWebhookJob;
use App\Models\Report;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JustinKluever\DiscordWebhookBuilder\Webhook;

#[Queue('moderation')]
class DeleteReportWebhookJob extends BaseDiscordWebhookJob implements ShouldBeUnique
{
    public function __construct(
        private readonly int $messageId,
        public readonly ?Report $report = null,
    ) {
        if ($this->report instanceof Report && $this->report->discord_message_id !== $this->messageId) {
            throw new InvalidArgumentException('Discord message id must belong to provided Report');
        }
    }

    public function uniqueId(): string
    {
        return $this->cacheKey('delete');
    }

    protected function getRequest(): PendingRequest|Response
    {
        return Http::timeout(5)->delete($this->getWebhook(), $this->getPayload());
    }

    protected function getPayload(): Webhook
    {
        return Webhook::make();
    }

    protected function handleResponse(Response $response): void
    {
        $this->report?->update([
            'discord_message_id' => null,
        ]);

        $this->preventFutureAttempts();
    }

    protected function handleWebhookNotFound(Response $response): ?bool
    {
        Log::debug('Report Message got removed already', [
            'webhook_id' => $this->getWebhookId(),
            'message_id' => $this->getWebhookMessageId(),
        ]);

        $this->report?->update([
            'discord_message_id' => null,
        ]);

        $this->preventFutureAttempts();

        return true;
    }

    protected function getWebhook(): string
    {
        return config('services.discord.webhooks.moderation')."/messages/{$this->getMessageId()}";
    }

    private function getMessageId(): int
    {
        return $this->report?->discord_message_id ?? $this->messageId;
    }
}
