<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\CarbonInterval;
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

        $votes = Cache::remember(
            $cacheKey.'.votes',
            now()->addMinute(),
            fn () => $user
                ->votes()
                ->count()
        );
        $votes30Days = Cache::remember(
            $cacheKey.'.votes30Days',
            now()->addMinute(),
            fn () => $user
                ->votes()
                ->where('created_at', '>=', now()->sub(CarbonInterval::fromString(('30 days'))))
                ->count()
        );

        return view('statistics', [
            'clipsSubmitted' => $clipsSubmitted,
            'votes' => $votes,
            'votes30Days' => $votes30Days,
        ]);
    }
}
