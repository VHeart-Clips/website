<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\Twitch\Data\GameDto;
use App\Services\Twitch\Enums\TwitchEndpoints;
use App\Services\Twitch\Exceptions\TwitchApiException;
use App\Services\Twitch\TwitchService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;

#[Signature('twitch:sync-live-categories')]
#[Description('Updates local categories based on what is currently live on twitch. only fetches first 100 streams though.')]
class SyncLiveCategoriesCommand extends Command
{
    /**
     * @throws TwitchApiException
     * @throws ConnectionException
     */
    public function handle(TwitchService $twitchService): void
    {
        $client = $twitchService->asApp();

        /** @var positive-int[] $gameIds */
        $gameIds = collect($client->collection(TwitchEndpoints::GetStreams, ['first' => 100, 'language' => 'de']))
            ->pluck('gameId')
            ->unique()
            ->filter(fn (int $value): bool => $value > 0)
            ->values()
            ->toArray();

        if (empty($gameIds)) {
            $this->info('No live streams found.');

            return;
        }

        /** @var Collection<GameDto> $games */
        $games = collect($client->collection(TwitchEndpoints::GetGames, ['id' => $gameIds]));

        $records = $games->map(fn (GameDto $dto): array => ['id' => $dto->id, ...$dto->toModel()])->all();
        Category::upsert($records, ['id']);

        $this->info("Updated {$games->count()} categories.");
    }
}
