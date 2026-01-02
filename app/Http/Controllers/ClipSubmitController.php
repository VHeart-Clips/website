<?php

namespace App\Http\Controllers;

use App\Models\Clip;
use App\Models\Clip\Tag;
use App\Models\Game;
use App\Models\User;
use App\Services\Twitch\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ClipSubmitController extends Controller
{
    private TwitchService $twitchService;

    public function __construct()
    {
        $this->twitchService = new TwitchService;
        $this->twitchService->onUserTokenRefresh(function ($token) {
            session()->put('twitch_access_token', $token);
        });
    }

    /**
     * Show the form for creating the resource.
     */
    public function create(Request $request): Response
    {
        $request->validate(['clip_url' => ['sometimes', 'string', 'url']]);
        $clipUrl = $request->string('clip_url');
        $preview = null;

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
        Gate::authorize('submit', Clip::class);

        $data = $request->validate(rules: [
            'clip_url' => ['required', 'string', 'url'],
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
            throw ValidationException::withMessages(['clip_url' => 'Clip bereits bekannt']);
        }

        $clipInfos = $this->twitchService->asUser($user, $this->getUserToken())->get(TwitchEndpoints::GetClips, ['id' => $clipId]);

        if (empty($clipInfos['data'])) {
            throw ValidationException::withMessages(['clip_url' => 'Clip nicht gefunden']);
        }

        $clipInfo = $clipInfos['data'][0];

        $broadcasterId = (int) $clipInfo['broadcaster_id'];

        $broadcasterUser = User::query()->find($broadcasterId);

        if (empty($broadcasterUser) || $broadcasterUser->clip_permission == false) {
            throw ValidationException::withMessages(['clip_url' => 'Broadcaster hat Einsendungen nicht erlaubt']);
        }

        $twitchClipperId = $clipInfo['creator_id'];

        $isUserBlackedListed = $broadcasterUser->broadcasterFilter()->where('user_id', '=', $twitchClipperId)
            ->where('allowed', false)
            ->first();

        if (! empty($isUserBlackedListed)) {
            throw ValidationException::withMessages(['clip_url' => 'Du darfts keine Clips einsenden für den Streamer']);
        }

        $userRules = $broadcasterUser->rules ?? [];
        $userAllowed = empty($userRules); // || $broadcasterId === $user->id;

        if (! $userAllowed && in_array('userAllowList', haystack: $userRules)) {
            $isUserWhiteListed = $broadcasterUser->broadcasterFilter()->where('user_id', '=', $twitchClipperId)
                ->where('allowed', true)
                ->first();

            if ($isUserWhiteListed) {
                $userAllowed = true;
            }
        }

        if (! $userAllowed && in_array('userAllowMods', $userRules)) {
            if ($this->twitchService->asUser($user, $this->getUserToken())->isModeratorFor($broadcasterUser)) {
                $userAllowed = true;
            }
        }

        if (! $userAllowed && in_array('userAllowVips', $userRules)) {
            try {
                $vipInfos = $this->twitchService->asUser($broadcasterUser)->onUserTokenRefresh()->get(TwitchEndpoints::GetVIPs, [
                    'user_id' => $user->id,
                    'broadcaster_id' => $broadcasterId,
                ]);

                if (! empty($vipInfos['data'])) {
                    $userAllowed = true;
                }

            } catch (\App\Services\Twitch\Exceptions\TwitchApiException $th) {
                throw ValidationException::withMessages(['clip_url' => 'errro getting vip'.$th->getMessage()]);
            }
        }

        if (! $userAllowed) {
            throw ValidationException::withMessages(['clip_url' => 'Du darfts keine Clips einsenden für den Streamer']);
        }

        $twitchClipper = User::updateOrCreate([
            'id' => $twitchClipperId,
        ], [
            'name' => $clipInfo['creator_name'],
        ]);

        $gameId = $clipInfo['game_id'];

        $game = Game::find($gameId);

        if (empty($game)) {
            $gameInfos = $this->twitchService->asUser($user, $this->getUserToken())->get(TwitchEndpoints::GetGames, ['id' => $gameId]);

            if (empty($gameInfos['data'])) {
                throw ValidationException::withMessages(['clip_url' => 'Game vom Clip nicht gefunden']);
            }

            $gameInfo = $gameInfos['data'][0];

            $game = Game::create([
                'id' => $gameId,
                'title' => $gameInfo['name'],
                'box_art' => $gameInfo['box_art_url'],
            ]);
        }

        Clip::create([
            'twitch_id' => $clipId,
            'title' => $clipInfo['title'],
            'url' => $clipInfo['url'],
            'thumbnail_url' => $clipInfo['thumbnail_url'],
            'broadcaster_id' => $broadcasterId,
            'creator_id' => $twitchClipperId,
            'submitter_id' => $user->id,
            'game_id' => $clipInfo['game_id'],
            'vod_id' => empty($clipInfo['video_id']) ? null : $clipInfo['video_id'],
            'vod_offset' => empty($clipInfo['vod_offset']) ? null : $clipInfo['vod_offset'],
            'duration' => $clipInfo['duration'],
            'language' => $clipInfo['language'],
            'date' => $clipInfo['created_at'],
        ]);

        return to_route('submitclip')->with('submit_ok', true)
            ->with('submit_message', __('sendinclip.flash.submitted'));
    }

    private function getClipIdFromUrl(string $clipUrl): ?string
    {
        if (preg_match('/https:\/\/(?:www.twitch.tv\/\w*\/clip|clips.twitch.tv)\/([\w_-]*)?.*/', $clipUrl, $m)) {
            return $m[1];
        }

        return null;
    }

    private function getUserToken(): string
    {
        return session()?->get('twitch_access_token');
    }
}
