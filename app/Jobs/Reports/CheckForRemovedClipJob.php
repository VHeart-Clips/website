<?php

declare(strict_types=1);

namespace App\Jobs\Reports;

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
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Throwable;

#[DeleteWhenMissingModels]
#[Tries(1)]
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
    public function handle(TwitchService $twitchService): void
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
        $this->deleteClip();
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

    private function deleteClip(): void
    {
        if ($this->clip->compilations()->exists()) {
            Log::debug('Clip belongs to compilations, soft deleting only.');
            $this->clip->delete();

            return;
        }

        Log::debug('Clip has no compilations, wiping completely.');
        $this->clip->votes()->forceDelete();
        $this->clip->comments()->forceDelete();
        $this->clip->tags()->detach();
        $this->clip->forceDelete();
    }

    private function cannotResolve(string $message): void
    {
        $this->report?->comment($message, User::find(0));
        $this->report?->update(['claimed_by' => null, 'claimed_at' => null]);
    }
}
