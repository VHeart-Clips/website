<?php

declare(strict_types=1);

namespace App\Queries\Leaderboard;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class UserLeaderboardVoteQuery
{
    public function handle(CarbonInterface $from, CarbonInterface $to, int $limit = 10): Collection
    {
        return User::query()
                ->where('id', '!=', 0)
                ->withCount([
                    'votes' => fn (Builder $q) => $q->whereBetween('created_at', [$from, $to]),
                ])
                ->withMax([
                    'votes' => fn (Builder $q) => $q->whereBetween('created_at', [$from, $to]),
                ], 'created_at')
                ->orderBy('votes_count', 'desc')
                ->orderBy('votes_max_created_at', 'asc')
                ->whereHas('votes', fn (Builder $q): Builder => $q->whereBetween('created_at', [$from, $to]))
                ->limit($limit)
                ->get();
    }
}
