<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Clips\CompilationStatus;
use App\Enums\ClipVoteType;
use App\Enums\Permission;
use App\Models\Clip;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClipVoteController extends Controller
{
    /**
     * Show the form for creating the resource.
     */
    public function create(Request $request)
    {
        $user = $request->user();

        return Inertia::render('evaluateclips', [
            'history' => Inertia::lazy(function () use ($user) {
                $lastVotes = Vote::where('user_id', $user->id)->limit(5)
                    ->with(['clip' => function (BelongsTo $query) {
                        $query->select(['id', 'twitch_id', 'title']);
                    }])
                    ->select(['id', 'clip_id', 'voted', 'created_at'])
                    ->orderBy('id', 'desc')
                    ->get();

                return $lastVotes;
            }),
            'clip' => Inertia::lazy(function () use ($user) {
                /** @var Clip $clip */
                $clip = Clip::whereDoesntHave('votes', function (Builder $query) use ($user) {
                    return $query->where('user_id', $user->id);
                })->whereNot('broadcaster_id', $user->id)
                    ->whereDoesntHave('compilations', function (Builder $query) {
                        $status = [
                            CompilationStatus::Scheduled,
                            CompilationStatus::Unlisted,
                            CompilationStatus::Published,
                            CompilationStatus::Archived,
                        ];

                        return $query->where('compilations.status', $status);
                    })->select(['id', 'twitch_id', 'title'])
                    ->withCount(['votes as public_votes' => function (Builder $query) {
                        $query->where('type', ClipVoteType::Public);
                    }])
                    ->inRandomOrder()->first();

                return $clip;
            }),
        ]);
    }

    /**
     * Store the newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'clip' => ['required', 'exists:clips,id'],
            'voted' => ['required', 'bool'],
        ]);

        $voteType = ClipVoteType::Public;
        if ($request->user()->can(Permission::JuryVote)) {
            $voteType = ClipVoteType::Jury;
        }

        $clip = Clip::find($data['clip']);
        $clip->votes()->updateOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'voted' => $data['voted'],
            'type' => $voteType,
        ]);

        return $this->create($request)->with('voted_ok', true);
    }
}
