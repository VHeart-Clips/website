<?php

declare(strict_types=1);

namespace App\Events\Discord;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Will be dispatched if a discord webhook triggered a 404 error on discord
 */
class DiscordWebhookDied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $webhookId,
        public string $webhookUrl
    ) {
        //
    }
}
