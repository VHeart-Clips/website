<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Leaderboard\LeaderboardRange;
use App\Models\User;
use App\Queries\Leaderboard\UserLeaderboardSubmitterQuery;
use App\Queries\Leaderboard\UserLeaderboardVoteQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use stdClass;

class LeaderboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $selectedRange = $request->enum('range', LeaderboardRange::class, LeaderboardRange::ThisWeek);

        $start = $selectedRange->getTimestampFrom();
        $end = $selectedRange->getTimestampTo();

        $topSubmitters = Cache::remember(
            'leaderboard.top.submitter.'.$selectedRange->getCacheKey(),
            now()->addHour(),
            fn (): array => [
                'timestamp' => now(),
                'users' => new UserLeaderboardSubmitterQuery()->handle($start, $end, config('vheart.leaderboards.submitter.limit')),
            ]
        );

        $topVoters = Cache::remember(
            'leaderboard.top.voter.'.$selectedRange->getCacheKey(),
            now()->addHour(),
            fn (): array => [
                'timestamp' => now(),
                'users' => new UserLeaderboardVoteQuery()->handle($start, $end, config('vheart.leaderboards.submitter.limit')),
            ]);

        $ids = collect($topSubmitters['users']->pluck('id'))->merge($topVoters['users']->pluck('id'))->unique()->values();

        $users = User::whereIn('id', $ids)->get(['id', 'name', 'avatar_url']);

        $topSubmitters['users'] = $topSubmitters['users']->map(function (array $user) use ($users): stdClass {
            $data = $users->first(fn (User $item): bool => $item->id === $user['id']);

            $data->count = $user['count'];

            return (object) $data->toArray();
        });

        $topVoters['users'] = $topVoters['users']->map(function (array $user) use ($users): stdClass {
            $data = $users->first(fn (User $item): bool => $item->id === $user['id']);

            $data->count = $user['count'];

            return (object) $data->toArray();
        });

        return view('leaderboard', [
            'ranges' => LeaderboardRange::cases(),
            'selectedRange' => $selectedRange,
            'topSubmitters' => $topSubmitters,
            'topVoters' => $topVoters,
        ]);
    }
}
