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

#[Signature('twitch:sync-top-games')]
#[Description('Updates local categories based on top 100 games on twitch.')]
class SyncTopGamesCommand extends Command
{
    /**
     * @throws TwitchApiException
     * @throws ConnectionException
     */
    public function handle(TwitchService $twitchService): void
    {
        $client = $twitchService->asApp();

        /** @var Collection<GameDto> $games */
        $games = collect($client->collection(TwitchEndpoints::GetTopGames, ['first' => 100]));

        $records = $games->map(fn (GameDto $dto): array => ['id' => $dto->id, ...$dto->toModel()])->all();
        Category::upsert($records, ['id']);

        $this->info("Updated {$games->count()} categories.");
    }
}
