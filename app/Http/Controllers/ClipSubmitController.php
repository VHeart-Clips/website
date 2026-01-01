<?php

namespace App\Http\Controllers;

use App\Models\Clip;
use App\Models\Clip\Tag;
use App\Models\User;
use App\Services\Twitch\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ClipSubmitController extends Controller
{
    private TwitchService $twitchService;

    public function __construct(TwitchService $twitchService)
    {
        $this->twitchService = $twitchService;
    }

    /**
     * Show the form for creating the resource.
     */
    public function create(Request $request): Response
    {
        $clipUrl = (string) $request->query('clip_url', default: '');
        $preview = null;

        Log::info($clipUrl);

        if ($clipUrl !== '') {
            $clipId = $this->getClipIdFromUrl($clipUrl);

            if ($clipId) {
                $parent = $request->getHost();

                $preview = [
                    'ok' => true,
                    'can_submit' => true,
                    'clip' => [
                        'clip_id' => $clipId,
                        'embed_url' => "https://clips.twitch.tv/embed?clip={$clipId}&parent={$parent}",
                        'broadcaster_login' => null,
                        'game_name' => null,
                    ],
                ];
            } else {
                $preview = [
                    'ok' => false,
                    'can_submit' => false,
                    'errors' => [__('sendinclip.errors.invalid_clip_url')],
                ];
            }
        }

        $tags = Tag::all()->toArray();

        return Inertia::render('submitclip', [
            'preview' => $preview,
            'tags' => $tags,
        ]);
    }

    /**
     * Store the newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(rules: [
            'clip_url' => ['required', 'string'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer'],
            'is_anonymous' => ['sometimes', 'boolean'],
        ]);

        $clipUrl = $data['clip_url'];
        $clipId = $this->getClipIdFromUrl($clipUrl);
        $tagIds = $data['tag_ids'] ?? [];
        $isAnonymous = (bool) ($data['is_anonymous'] ?? false);

        $user = Auth::user();

        $clipModel = Clip::where('twitch_id', '=', $clipId)->get();

        if ($clipModel->isNotEmpty()) {
            return to_route('submitclip')->withErrors(['clip_url' => 'Clip bereits bekannt']);
        }

        $clipInfos = $this->twitchService->asUser($user, session()->get('twitch_access_token'))->get(TwitchEndpoints::GetClips, ['id' => $clipId]);

        $clipInfo = empty($clipInfos['data']) ? [] : $clipInfos['data'][0];

        $broadcasterId = $clipInfo['broadcaster_id'];

        $broadcasterUser = User::find($broadcasterId);

        if (empty($broadcasterUser) || $broadcasterUser->clip_permission == false) {
            return to_route('submitclip')->withErrors(['clip_url' => 'Broadcaster hat Einsendungen nicht erlaubt']);
        }

        $twitchClipper = $clipInfo;

        dd($clipInfo);

        return to_route('submitclip')
            ->with('submit_ok', true)
            ->with('submit_message', __('sendinclip.flash.submitted'));
    }

    private function getClipIdFromUrl(string $clipUrl): ?string
    {
        if (preg_match('/https:\/\/(?:www.twitch.tv\/\w*\/clip|clips.twitch.tv)\/([\w_-]*)?.*/', $clipUrl, $m)) {
            return $m[1];
        }

        return null;
    }
}
