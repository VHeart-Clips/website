<?php

declare(strict_types=1);

namespace App\Jobs\Discord\Compilation\Status;

use App\Jobs\Discord\BaseDiscordWebhookJob;
use App\Jobs\Discord\Traits\DeletesDiscordWebhookMessage;
use App\Models\Clip\Compilation;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\Attributes\Delay;
use Illuminate\Queue\Attributes\WithoutRelations;

#[Delay(5)]
#[WithoutRelations]
class DeleteCompilationStatusWebhookJob extends BaseDiscordWebhookJob implements ShouldBeUnique
{
    use DeletesDiscordWebhookMessage;

    public function __construct(
        private readonly int $messageId,
    ) {}

    protected function handleResponse(Response $response): void
    {
        $this->removeMessageId();
        $this->preventFutureAttempts();
    }

    protected function handleWebhookNotFound(Response $response): ?bool
    {
        $this->removeMessageId();
        $this->preventFutureAttempts();

        return true;
    }

    protected function getWebhook(): string
    {
        return config('services.discord.webhooks.compilation')."/messages/$this->messageId";
    }

    private function removeMessageId(): void
    {
        Compilation::query()
            ->where('discord_message_id', $this->messageId)
            ->withTrashed()
            ->update([
                'discord_message_id' => null,
            ]);
    }
}
