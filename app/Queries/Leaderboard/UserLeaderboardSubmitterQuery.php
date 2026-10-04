<?php

namespace App\Queries\Leaderboard;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class UserLeaderboardSubmitterQuery
{
    public function handle(CarbonInterface $from, CarbonInterface $to, int $limit = 10): Collection
    {
        return User::query()
                ->where('id', '!=', 0)
                ->withCount([
                    'submittedClips' => fn (Builder $q) => $q->whereBetween('created_at', [$from, $to]),
                ])
                ->withMax([
                    'submittedClips' => fn (Builder $q) => $q->whereBetween('created_at', [$from, $to]),
                ], 'created_at')
                ->orderBy('submitted_clips_count', 'desc')
                ->orderBy('submitted_clips_max_created_at', 'asc')
                ->whereHas('submittedClips', fn (Builder $q): Builder => $q->whereBetween('created_at', [$from, $to]))
                ->limit($limit)
                ->get();
    }
}
