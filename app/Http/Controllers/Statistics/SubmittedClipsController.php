<?php

declare(strict_types=1);

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use App\Models\Clip;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SubmittedClipsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response|View
    {
        $submittedClips = Clip::query()
            ->where('submitter_id', $request->user()->id)
            ->withAbsoluteVoteCount()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(perPage: 32);

        if ($request->ajax()) {
            return response(
                view('statistics.submitted-clips-list', [
                    'submittedClips' => $submittedClips,
                ])->render(),
                headers: [
                    'X-Next-Page' => $submittedClips->nextPageUrl(),
                ]);
        }

        return view('statistics.submitted-clips', [
            'submittedClips' => $submittedClips,
        ]);
    }
}
