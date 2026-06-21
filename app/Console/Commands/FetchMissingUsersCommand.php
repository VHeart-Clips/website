<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Broadcaster\Broadcaster;
use App\Models\Clip;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\User;
use App\Services\Twitch\Data\UserDto;
use App\Services\Twitch\TwitchService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

#[Signature('app:fetch-missing-users')]
#[Description('Fetch (missing) users and create broadcaster profiles with consent for testing')]
class FetchMissingUsersCommand extends Command
{
    public function handle(TwitchService $twitchService): void
    {
        $broadcasterIds = Clip::withoutGlobalScope(ClipPermissionScope::class)->doesntHave('broadcaster')->pluck('broadcaster_id');
        $creatorIds = Clip::withoutGlobalScope(ClipPermissionScope::class)->doesntHave('creator')->pluck('creator_id');
        $submitterIds = Clip::withoutGlobalScope(ClipPermissionScope::class)->doesntHave('submitter')->pluck('submitter_id');

        $missingIds = $broadcasterIds
            ->concat($creatorIds->toArray())
            ->concat($submitterIds->toArray())
            ->reject(fn (int $id): bool => $id === 0)
            ->filter()
            ->unique()
            ->values();

        if ($missingIds->isEmpty()) {
            $this->info('No missing users found.');

            return;
        }

        $totalBatches = ceil($missingIds->count() / 100);
        $currentBatch = 1;
        $twitchApp = $twitchService->asApp();

        $missingIds->chunk(100)->each(function (Collection $chunk) use ($twitchApp, &$currentBatch, &$totalBatches): void {
            $users = $twitchApp->getUsers([
                'id' => $chunk->values()->toArray(),
            ]);

            $fetchedIds = collect($users)->map(function (UserDto $user): string {
                User::firstOrCreate([
                    'id' => $user->id,
                ], $user->toModel());

                return $user->id;
            });

            $chunk->diff($fetchedIds)->each(function (int $id): void {
                $this->warn("User {$id} got removed or banned from twitch, using placeholder values.");

                $user = User::firstOrCreate([
                    'id' => $id,
                ], [
                    'name' => 'Deleted User',
                    'email' => null,
                    'avatar_url' => 'https://api.dicebear.com/9.x/pixel-art/svg?seed='.$id,
                ]);

                // Trigger Observers
                $user->delete();
            });

            $this->info("Batch {$currentBatch}/{$totalBatches} Finished");
            $currentBatch++;
        });

        $this->info("Fetched {$missingIds->count()} users.");

        $total = Broadcaster::upsert($missingIds->map(fn (int $id): array => ['id' => $id, 'consent' => '[0]'])->toArray(), ['id']);
        $this->info("Created {$total} broadcaster profiles.");
    }
}
