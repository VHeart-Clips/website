<?php

declare(strict_types=1);

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class VotesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response|View
    {
        $allowedVoteToChange = Vote::query()
            ->where('user_id', $request->user()->id)
            ->where('created_at', '>=', now()->sub(config('vheart.clips.voting.maximum_change_age')))
            ->has('clip')
            ->latest()
            ->orderByDesc('id')
            ->first();

        $votedInfos = Vote::query()
            ->where('user_id', $request->user()->id)
            ->has('clip')
            ->with('clip', fn (Relation $relation) => $relation->withAbsoluteVoteCount())
            ->latest()
            ->orderByDesc('id')
            ->cursorPaginate(perPage: 32);

        if ($request->ajax()) {
            return response(
                view('statistics.vote-list', [
                    'allowedVoteToChange' => $allowedVoteToChange,
                    'votes' => $votedInfos,
                ])->render(),
                headers: [
                    'X-Next-Page' => $votedInfos->nextPageUrl(),
                ]);
        }

        return view('statistics.votes', [
            'allowedVoteToChange' => $allowedVoteToChange,
            'votes' => $votedInfos,
        ]);
    }
}
