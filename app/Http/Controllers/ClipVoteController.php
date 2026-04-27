<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GenerateVotingQueueAction;
use App\Enums\ClipVoteType;
use App\Enums\Permission;
use App\Http\Resources\Clip\ClipVoteResource;
use App\Models\Clip;
use App\Models\Scopes\ClipPermissionScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

#[Middleware('throttle:10,1', only: ['store'])]
class ClipVoteController extends Controller
{
    private const string SESSION_QUEUE_KEY = 'CLIP_VOTE_QUEUE';

    public function __construct(private readonly GenerateVotingQueueAction $generateVotingQueue) {}

    /**
     * Show the form for creating the resource.
     */
    public function create(Request $request): View
    {
        $clip = $this->resolveNextClip($request);

        return view('clips.vote', [
            'clip' => $clip,
        ]);
    }

    /**
     * Store the newly created resource in storage.
     */
    public function store(Request $request): SymfonyResponse
    {
        $request->validate([
            'voted' => ['required', 'bool'],
        ]);

        $vote = $request->boolean('voted');
        $clipId = $this->getNextClipId($request);
        $this->shiftClipQueue($request);

        $clip = Clip::query()
            ->whereNotPublished()
            ->find($clipId);

        if ($clip) {
            $voteType = $request->user()->can(Permission::JuryVote)
                ? ClipVoteType::Jury
                : ClipVoteType::Public;

            $clip->votes()->updateOrCreate([
                'user_id' => $request->user()->id,
            ], [
                'voted' => $vote,
                'type' => $voteType,
            ]);
        }

        if (! $request->expectsJson()) {
            return back(fallback: route('vote'));
        }

        $nextClip = $this->resolveNextClip($request);

        return new JsonResponse($nextClip?->toResource(ClipVoteResource::class));
    }

    protected function resolveNextClip(Request $request): ?Clip
    {
        while ($clipId = $this->getNextClipId($request)) {
            if ($clip = Clip::query()
                ->withoutGlobalScope(ClipPermissionScope::class)
                ->whereNoVotesFrom($request->user())
                ->find($clipId)
            ) {
                return $clip;
            }

            Log::debug('Clip not found, shifting to next clip in queue', ['clip_id' => $clipId]);
            $this->shiftClipQueue($request);
        }

        Log::debug('exhausted all possible options, giving up on getting a clip');

        return null;
    }

    /**
     * Shifts the clip voting Queue
     */
    protected function shiftClipQueue(Request $request): void
    {
        $clipQueue = $this->getVoteQueue($request);

        if (! $clipQueue || $clipQueue === []) {
            return;
        }

        array_shift($clipQueue);
        $request->session()->put(self::SESSION_QUEUE_KEY, $clipQueue);
    }

    /**
     * Get the clip vote queue, if the queue is empty or does not exist we will also generate it here.
     */
    protected function getVoteQueue(Request $request): array
    {
        $session = $request->session();
        $clips = $session->get(self::SESSION_QUEUE_KEY, []);

        if ($clips !== []) {
            return $clips;
        }

        $clips = $this->generateVotingQueue->execute($request->user());

        if ($clips !== []) {
            $session->put(self::SESSION_QUEUE_KEY, $clips);
        }

        return $clips;
    }

    /**
     * Get the next clip id in the queue.
     *
     * Will return `null` if there is nothing the user can vote on anymore.
     */
    protected function getNextClipId(Request $request): ?int
    {
        $voteQueue = $this->getVoteQueue($request);

        return $voteQueue[0] ?? null;
    }
}
