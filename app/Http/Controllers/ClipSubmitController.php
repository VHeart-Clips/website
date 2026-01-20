<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ImportClipAction;
use App\Models\Clip;
use App\Models\Clip\Tag;
use App\Models\Game;
use App\Models\User;
use App\Services\Twitch\Exceptions\TwitchApiException;
use App\Services\Twitch\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Illuminate\Http\Request;
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
    public function create(): Response
    {
        $tags = Tag::all();

        return Inertia::render('submitclip', [
            'tags' => $tags,
        ]);
    }

    /**
     * Store the newly created resource in storage.
     */
    public function store(Request $request, ImportClipAction $importClipAction)
    {
        Gate::authorize('submit', Clip::class);

        $data = $request->validate(rules: [
            'clip_url' => ['required', 'string', 'url'],
            'tags' => ['required', 'array', 'min:1', 'max:3'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'is_anonymous' => ['sometimes', 'accepted'],
        ]);

        $clipId = $this->twitchService->parseClipId($data['clip_url']);

        if (!$clipId) {
            $this->returnError('sendinclip.errors.clip_not_found');
        }

        $tagIds = $data['tags'] ?? [];
        $isAnonymous = ($data['is_anonymous'] ?? "off") === "on";

        $user = $request->user();

        $clipModel = Clip::where('twitch_id', $clipId)->get();

        if ($clipModel->isNotEmpty()) {
            $this->returnError('sendinclip.errors.clip_already_known');
        }

        $clipInfo = $this->twitchService->asUser($user, $this->getUserToken())->getClipByID($clipId);

        if (!$clipInfo) {
            $this->returnError('sendinclip.errors.clip_not_found');
        }

        $broadcasterUser = User::query()->find($clipInfo->broadcaster_id);

        if (empty($broadcasterUser) || $broadcasterUser->clip_permission === false) {
            $this->returnError('sendinclip.errors.broadcaster_not_allowed');
        }

        $isUserBlackedListed = $broadcasterUser->broadcasterUserFilter()->where(column: 'filter_id', operator: $user->id)
            ->where('allowed', false)
            ->first();

        if (! empty($isUserBlackedListed)) {
            $this->returnError('sendinclip.errors.user_not_allowed_for_broadcaster');
        }

        $broadcasterRules = $broadcasterUser->rules ?? [];
        $userIsAllowed = empty($broadcasterRules) || $clipInfo->broadcaster_id === $user->id;

        if (! $userIsAllowed && in_array('userAllowList', $broadcasterRules)) {
            $isUserWhiteListed = $broadcasterUser->broadcasterUserFilter()->where('user_id', $clipInfo->creator_id)
                ->where('allowed', true)
                ->first();

            if ($isUserWhiteListed) {
                $userIsAllowed = true;
            }
        }

        if (! $userIsAllowed && in_array('userAllowMods', $broadcasterRules)) {
            if ($this->twitchService->asUser($user, $this->getUserToken())->isModeratorFor($broadcasterUser)) {
                $userIsAllowed = true;
            }
        }

        if (! $userIsAllowed && in_array('userAllowVips', $broadcasterRules)) {
            try {
                $vipInfos = $this->twitchService->asUser($broadcasterUser)->onUserTokenRefresh()->get(TwitchEndpoints::GetVIPs, [
                    'user_id' => $user->id,
                    'broadcaster_id' => $clipInfo->broadcaster_id,
                ]);

                if (! empty($vipInfos['data'])) {
                    $userIsAllowed = true;
                }

            } catch (TwitchApiException $th) {
                report($th);
            }
        }

        if (! $userIsAllowed) {
            $this->returnError('sendinclip.errors.user_not_allowed_for_broadcaster');
        }

        User::updateOrCreate([
            'id' => $clipInfo->creator_id,
        ], [
            'name' => $clipInfo->creator_name,
        ]);

        $isGameBlackListed = $broadcasterUser->broadcasterGameFilter()->where('filter_id', $clipInfo->game_id)
            ->where('allowed', false)
            ->first();

        if (! empty($isGameBlackListed)) {
            $this->returnError('sendinclip.errors.game_blocked');
        }

        $hasOneGameWhiteListed = $broadcasterUser->broadcasterGameFilter()->where('allowed', true)->exists();
        $isGameWhiteListed = $broadcasterUser->broadcasterGameFilter()->where('filter_id', $clipInfo->game_id)
            ->where('allowed', true)
            ->first();

        if ($hasOneGameWhiteListed && ! $isGameWhiteListed) {
            $this->returnError('sendinclip.errors.game_blocked');
        }

        $importClipAction->execute(
            $clipInfo,
            $request->user(),
            $isAnonymous,
            $tagIds
        );

        return $this->create()
            ->with('submit_ok', true)
            ->with('submit_message', __('sendinclip.flash.submitted'));
    }

    private function getUserToken(): string
    {
        return session()?->get('twitch_access_token');
    }

    /**
     * Summary of returnError
     *
     * @return never
     *
     * @throws ValidationException;
     */
    private function returnError(string $errorKey): void
    {
        throw ValidationException::withMessages(['clip_url' => __($errorKey)]);
    }
}
