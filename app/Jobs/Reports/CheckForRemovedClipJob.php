<?php

declare(strict_types=1);

namespace App\Jobs\Reports;

use App\Actions\DeleteClipAction;
use App\Enums\Reports\ReportStatus;
use App\Enums\Reports\ResolveAction;
use App\Models\Clip;
use App\Models\Report;
use App\Models\User;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\TwitchService;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Tries(254)]
#[MaxExceptions(3)]
#[DeleteWhenMissingModels]
class CheckForRemovedClipJob implements ShouldBeUnique, ShouldDispatchAfterCommit, ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Clip $clip,
        public ?Report $report,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function handle(TwitchService $twitchService, DeleteClipAction $deleteClip): void
    {
        Context::add('clip_id', $this->clip->id);
        Context::add('clip_slug', $this->clip->twitch_id);

        $this->claim();

        Log::debug('Checking for Clip availability...');

        if ($twitchService->asApp()->getClip($this->clip->twitch_id) instanceof ClipDto) {
            Log::debug('Clip found on Twitch');
            $this->cannotResolve('Clip has been found on Twitch, manual moderation required.');

            return;
        }

        Log::debug('Clip removed from Twitch, resolving...');
        $this->resolve();
        $deleteClip->execute($this->clip);
    }

    public function failed(?Throwable $exception): void
    {
        $this->cannotResolve('Encountered an error while checking availability, manual moderation required.');
    }

    public function uniqueId(): string
    {
        return (string) $this->clip->id;
    }

    public function middleware(): array
    {
        return [
            new RateLimited('jobs:reports:check-removed-clip')->dontRelease(),
            new RateLimited('twitch-api'),
            new ThrottlesExceptions(
                maxAttempts: 3,
                decaySeconds: 15
            ),
        ];
    }

    private function claim(): void
    {
        $this->report?->update([
            'claimed_by' => 0,
            'claimed_at' => now(),
        ]);
    }

    private function resolve(): void
    {
        $this->report?->update([
            'status' => ReportStatus::Resolved,
            'resolved_by' => 0,
            'resolved_at' => now(),
            'resolve_action' => ResolveAction::ContentRemoved,
            'resolve_description' => 'Clip was removed from Twitch. (Automatically Resolved)',
            'deleted_at' => now(),
        ]);
    }

    private function cannotResolve(string $message): void
    {
        $this->report?->comment($message, User::find(0));
        $this->report?->update(['claimed_by' => null, 'claimed_at' => null]);
    }
}
