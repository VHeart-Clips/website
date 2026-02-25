<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ImportClipAction;
use App\Models\User;
use App\Services\Twitch\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Illuminate\Console\Command;

class ImportTopClipsFromTwitchCommand extends Command
{
    public const int GameCategory = 509658;

    protected $signature = 'app:import-top-clips-from-twitch';

    protected $description = 'Import top 20 clips from twitch (just chatting)';

    public function handle(TwitchService $twitchService, ImportClipAction $importClipAction): void
    {
        $clips = $twitchService->get(TwitchEndpoints::GetClips, [
            'game_id' => self::GameCategory,
        ]);

        foreach ($clips as $clip) {
            $importClipAction->execute($clip, User::first());
        }
    }
}
