<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\FeatureFlag;
use App\Models\Clip;
use App\Support\FeatureFlag\Feature;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class IndexController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke(Request $request): Response|View
    {
        if (Feature::isActive(FeatureFlag::AboutUsAsIndex)) {
            return view('about-us');
        }

        $bestRated = Clip::query()
            ->where('created_at', '>', now()->subDays(30))
            ->whereNotBlocked()
            ->where(fn (Builder $builder) => $builder->whereHas('votes', fn (Builder $q) => $q->where('voted', true))
                ->whereNotNull(['final_jury_votes', 'final_public_votes', 'final_score'], 'or'))
            ->with('tags')
            ->withAbsoluteVoteCount()
            ->orderByDesc('absolute_votes')
            ->limit(10)
            ->get();

        $discover = Clip::query()
            ->with('tags')
            ->whereNotBlocked()
            ->withAbsoluteVoteCount()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(perPage: 42);

        if ($request->ajax()) {
            return response(
                view('components.clips.preview-list', ['clips' => $discover])->render(),
                headers: [
                    'X-Next-Page' => $discover->nextPageUrl(),
                ]);
        }

        return view('index', [
            'bestRated' => $bestRated,
            'discover' => $discover,
        ]);
    }
}
