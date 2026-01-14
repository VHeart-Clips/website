<?php

namespace App\Http\Controllers;

use App\Enums\ClipVoteType;
use App\Enums\Permission;
use App\Models\Clip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClipVoteController extends Controller
{

    /**
     * Show the form for creating the resource.
     */
    public function create(Request $request)
    {
        return Inertia::render('evaluateclips',[
            'clip' => Inertia::lazy(function () use ($request) {
                $user = $request->user();

                /** @var Clip $clip */
                $clip = Clip::whereDoesntHave('votes',function(Builder $query) use ($user) {
                    return $query->where('user_id',$user->id);
                })->whereNot('broadcaster_id',$user->id)
                ->select(['id','twitch_id','title'])
                ->withCount(['votes as public_votes' => function (Builder $query) {
                    $query->where('type', ClipVoteType::Public);
                }])
                ->inRandomOrder()->first();

                return $clip;
            })
        ]);
    }

    /**
     * Store the newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            "clip"=> ['required', 'exists:clips,id'],
            "voted" => ["required", "bool"],
        ]);

        $voteType = ClipVoteType::Public;
        if($request->user()->can(Permission::JuryVote))
        {
            $voteType = ClipVoteType::Jury;
        }

        $clip = Clip::find($data['clip']);
        $clip->votes()->updateOrCreate([
            'user_id' => $request->user()->id
        ],[
            'voted' => $data['voted'],
            'type' => $voteType
        ]);

        return $this->create($request)->with('voted_ok',true);
    }

    /**
     * Remove the resource from storage.
     */
    public function destroy(): never
    {
        abort(404);
    }
}
