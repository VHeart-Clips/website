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
use Illuminate\Support\Facades\Gate;

class ClipSubmitController extends Controller
{
    public function create(): View
    {
        $tags = Tag::query()
            ->whereLocale('name', app()->getLocale())
            ->get();

        return view('clips.submit', ['tags' => $tags]);
    }

    public function store(SubmitClipRequest $request, ImportClipAction $importClipAction): RedirectResponse
    {
        Gate::authorize('submit', Clip::class);

        $clipInfo = $request->clipInfo;

        User::updateOrCreate([
            'id' => $clipInfo->creator_id,
        ], [
            'name' => $clipInfo->creator_name,
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
