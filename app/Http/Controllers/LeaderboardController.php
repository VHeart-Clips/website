<?php

namespace App\Http\Controllers;

use App\Enums\Leaderboard\LeaderboardRange;
use App\Queries\Leaderboard\UserLeaderboardSubmitterQuery;
use App\Queries\Leaderboard\UserLeaderboardVoteQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{

    public function __invoke(Request $request): View
    {
    $selectedRange = $request->enum("range",LeaderboardRange::class,LeaderboardRange::ThisWeek);

    $start = $selectedRange->getTimestampFrom();
    $end = $selectedRange->getTimestampTo();

    $topSubmitters = Cache::remember(
        'leaderboard.top.submitter.'.$selectedRange->getCacheKey(),
        now()->addHour(),
        fn (): array => [
            'timestamp' => now(),
            'users' => new UserLeaderboardSubmitterQuery()->handle($start,$end)
        ]
    );

    $topVoters = Cache::remember(
        'leaderboard.top.voter.'.$selectedRange->getCacheKey(),
        now()->addHour(),
        fn (): array => [
            'timestamp' => now(),
            'users' =>  new UserLeaderboardVoteQuery()->handle($start,$end),
        ]);

    return view('leaderboard', [
        'ranges' => LeaderboardRange::cases(),
        'selectedRange' => $selectedRange,
        'topSubmitters' => $topSubmitters,
        'topVoters' => $topVoters,
    ]);
    }
}
