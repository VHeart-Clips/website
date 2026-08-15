<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $cacheKey = "user.{$user->id}.statistics";
        $clipsSubmitted = Cache::remember(
            $cacheKey.'.clipsSubmitted',
            now()->addMinute(),
            fn () => $user
                ->submittedClips()
                ->count()
        );
        $clipsSubmittedWeek = Cache::remember(
            $cacheKey.'.clipsSubmittedWeek',
            now()->addMinute(),
            fn () => $user
                ->submittedClips()
                ->where('created_at', '>=', now()->startOfWeek())
                ->count()
        );

        $votes = Cache::remember(
            $cacheKey.'.votes',
            now()->addMinute(),
            fn () => $user
                ->votes()
                ->whereConsideredStable()
                ->count()
        );

        $votesWeek = Cache::remember(
            $cacheKey.'.votesWeek',
            now()->addMinute(),
            fn () => $user
                ->votes()
                ->whereConsideredStable()
                ->where('created_at', '>=', now()->startOfWeek())
                ->count()
        );

        $votes30Days = Cache::remember(
            $cacheKey.'.votes30Days',
            now()->addMinute(),
            fn () => $user
                ->votes()
                ->whereConsideredStable()
                ->where('created_at', '>=', now()->subDays(30))
                ->count()
        );

        return view('statistics', [
            'clipsSubmitted' => $clipsSubmitted,
            'clipsSubmittedWeek' => $clipsSubmittedWeek,
            'votes' => $votes,
            'votesWeek' => $votesWeek,
            'votes30Days' => $votes30Days,
        ]);
    }
}
