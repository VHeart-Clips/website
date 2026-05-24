<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions;

use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\TwitchService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Throwable;

class ClipSubmissionContext
{
    private ?ClipDto $clipDto = null;

    private Broadcaster|false|null $broadcaster = false;

    public function __construct(
        public readonly User $submitter,
        public readonly string $clipId,
        private readonly TwitchService $twitchService,
    ) {}

    public function clip(): ?ClipDto
    {
        try {
            // we allow it to retry up to 5 times in case of actual connection issues that are hopefully temporary lol
            return retry(5, function () {
                return $this->clipDto ??= $this->twitchService->asSessionUser()->getClip($this->clipId);
            }, 100, static function (Exception $exception) {
                return $exception instanceof ConnectionException;
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return null;
        }
    }

    public function broadcaster(): ?Broadcaster
    {
        if ($this->broadcaster === false) {
            $this->broadcaster = Broadcaster::query()
                ->where('id', $this->clip()?->broadcasterId)
                ->whereGaveConsent()
                ->with('filters')
                ->first();
        }

        return $this->broadcaster;
    }

    public function isSubmitterBroadcaster(): bool
    {
        return $this->clip()?->broadcasterId === $this->submitter->id;
    }
}
