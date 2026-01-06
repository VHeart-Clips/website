<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Clip;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * A Clip has been submitted duh
 *
 * Useful to trigger other stuff in the background later
 */
class ClipSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Clip $clip,
        public ?User $user = null,
        public ?bool $isAnonymous = false,
        public ?array $tags = []
    ) {
        Log::debug('Clip has been Submitted', [
            'clip_id' => $this->clip->id,
            'clip_slug' => $this->clip->twitch_id,
            'user_id' => $this->user?->id,
            'is_anonymous' => $this->isAnonymous,
            'tags' => $this->tags,
        ]);
    }
}
