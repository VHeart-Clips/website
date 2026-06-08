<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\ImportCategoryAction;
use App\Models\Clip;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Services\Twitch\Data\GameDto;
use App\Services\Twitch\Enums\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

#[Tries(254)]
#[MaxExceptions(3)]
class ImportCategoryJob implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new RateLimited('twitch-api'),
            new WithoutOverlapping(),
            new ThrottlesExceptions(
                maxAttempts: 3,
                decaySeconds: 15
            ),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(TwitchService $twitchService, ImportCategoryAction $importCategoryAction): void
    {
        $missingCategories = Clip::query()
            ->withoutGlobalScope(ClipPermissionScope::class)
            ->withoutGlobalScope(ClipWithoutBannedCategoryScope::class)
            ->whereDoesntHave('category')
            ->distinct('category_id')
            ->pluck('category_id');

        if ($missingCategories->isEmpty()) {
            Log::debug('No Categories missing.');

            return;
        }

        Log::debug('Fetching missing Categories.', [
            'total' => $missingCategories->count(),
        ]);

        $appTwitch = $twitchService->asApp();

        $missingCategories->chunk(100)->each(function ($chunk) use ($appTwitch, $importCategoryAction): void {
            $categories = $appTwitch->collection(TwitchEndpoints::GetGames, [
                'id' => $chunk->values()->toArray(),
            ]);

            foreach ($categories as $game) {
                /** @var GameDto $game */
                $importCategoryAction->execute($game);
            }

            Log::debug('Fetched Batch of Categories from Twitch', [
                'count' => count($categories),
            ]);

            // Let twitch breath to prevent 429
            sleep(1);
        });
    }
}
