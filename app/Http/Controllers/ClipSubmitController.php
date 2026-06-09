<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ImportClipAction;
use App\Http\Requests\SubmitClipRequest;
use App\Models\Clip;
use App\Models\Clip\Tag;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

#[Middleware('throttle:5,1', only: ['store'])]
class ClipSubmitController extends Controller
{
    public function create(Request $request): View
    {
        if ($ban = $request->user()->getBan()) {
            return view('clips.submit', [
                'tags' => [],
                'ban' => $ban,
                'unbanInMs' => $ban->banned_until ? now()->diffInMilliseconds($ban->banned_until) : -1,
            ]);
        }

        $tags = Tag::query()
            ->whereLocale('name', app()->getLocale())
            ->orderBy('order')
            ->get();

        return view('clips.submit', [
            'tags' => $tags,
            'ban' => null,
        ]);
    }

    public function store(SubmitClipRequest $request, ImportClipAction $importClipAction): RedirectResponse
    {
        Gate::authorize('submit', Clip::class);

        $clipInfo = $request->context->clip();

        User::updateOrCreate([
            'id' => $clipInfo->creatorId,
        ], [
            'name' => $clipInfo->creatorName,
        ]);

        $importClipAction->execute(
            $clipInfo,
            $request->user(),
            $request->validated('tags') ?? []
        );

        return to_route('submitclip.create')
            ->with('submit_ok', true)
            ->with('submit_message', __('clips.flash.submitted'));
    }
}
