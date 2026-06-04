<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\DeleteClipAction;
use App\Actions\UpdateClipAction;
use App\Models\Clip;
use App\Models\Scopes\ClipPermissionScope;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\Exceptions\TwitchApiException;
use App\Services\Twitch\TwitchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

#[Tries(254)]
#[MaxExceptions(3)]
#[Backoff([15, 30, 60, 300])]
class UpdateClipsBatchJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  positive-int[]  $clipIds
     *
     * @throws Throwable
     */
    public function __construct(
        public readonly array $clipIds,
        public ?array $columnsToUpdate = null,
        public readonly bool $shouldUpdateNextSyncAt = true
    ) {
        throw_if(
            count($this->clipIds) > 100,
            new InvalidArgumentException('UpdateClipsBatchJob only allows up to 100 clips at once')
        );
    }

    public function middleware(): array
    {
        return [
            new RateLimited('twitch-api'),
            new ThrottlesExceptions(
                maxAttempts: 3,
                decaySeconds: 15
            ),
        ];
    }

    /**
     * @throws Throwable
     * @throws TwitchApiException
     * @throws ConnectionException
     */
    public function handle(TwitchService $twitchService, UpdateClipAction $updateClipAction, DeleteClipAction $deleteClipAction): void
    {
        $clips = Clip::query()
            ->withoutGlobalScope(ClipPermissionScope::class)
            ->whereIn('id', $this->clipIds)
            ->get(['id', 'date', 'twitch_id']);

        if ($clips->isEmpty()) {
            return;
        }

        $clipDtos = $this->fetchClipDtos($twitchService, $clips->pluck('twitch_id')->all());

        DB::transaction(function () use ($clips, $clipDtos, $updateClipAction, $deleteClipAction): void {
            foreach ($clips as $clip) {
                $clipDto = $clipDtos->get($clip->twitch_id);

                if ($clipDto === null) {
                    Log::info('Clip removed on twitch, cleaning up...', ['clip_id' => $clip->id, 'clip_slug' => $clip->twitch_id]);
                    $deleteClipAction->execute($clip);

                    continue;
                }

                $updateClipAction->execute($clip, $clipDto, $this->columnsToUpdate ?? [
                    'title',
                    'thumbnail_url',
                ], updateNextSync: $this->shouldUpdateNextSyncAt);
            }
        });
    }

    /**
     * @param  string[]  $twitchIds
     * @return Collection<string, ClipDto>
     *
     * @throws ConnectionException
     * @throws TwitchApiException
     * @throws Throwable
     */
    private function fetchClipDtos(TwitchService $twitchService, array $twitchIds): Collection
    {
        return collect(
            retry(
                times: 3,
                callback: static fn (): array => $twitchService->asApp()->getClips(['id' => $twitchIds]),
                sleepMilliseconds: 500,
                when: static fn (Throwable $e): bool => $e instanceof ConnectionException,
            )
        )->keyBy('id');
    }
}
