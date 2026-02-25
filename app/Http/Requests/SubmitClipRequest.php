<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Clip;
use App\Models\User;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\Exceptions\TwitchApiException;
use App\Services\Twitch\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SubmitClipRequest extends FormRequest
{
    public ?ClipDto $clipInfo = null;

    public ?User $broadcaster = null;

    public ?array $disallowedUsers = null;

    public ?array $allowedUsers = null;

    public ?array $disallowedCategories = null;

    public ?array $allowedCategories = null;

    public ?string $clipId = null;

    public function __construct(
        protected TwitchService $twitchService
    ) {
        parent::__construct();

        $this->twitchService->onUserTokenRefresh(function ($token): void {
            session()?->put('twitch_access_token', $token);
        });
    }

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'clip_url' => ['bail', 'required', 'string', 'url'],
            'tags' => ['bail', 'required', 'array', 'min:1', 'max:3'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'parsed_clip_id.required' => __('sendinclip.errors.clip_not_found'),
            'parsed_clip_id.unique' => __('sendinclip.errors.clip_already_known'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->clipId = $this->twitchService->parseClipId($this->input('clip_url'));
                if (! $this->clipId) {
                    $validator->errors()->add('clip_url', __('sendinclip.errors.clip_not_found'));

                    return;
                }

                $this->clipInfo = $this->twitchService
                    ->asUser($this->user(), session()?->get('twitch_access_token'))
                    ->getClipByID($this->clipId);

                if (! $this->clipInfo instanceof ClipDto) {
                    $validator->errors()->add('clip_url', __('sendinclip.errors.clip_not_found'));

                    return;
                }

                // Check if the Category is Site-Banned
                $isCategoryBanned = Category::query()
                    ->where('is_banned', true)
                    ->where('id', $this->clipInfo->game_id)
                    ->exists();

                if ($isCategoryBanned) {
                    $validator->errors()->add('clip_url', __('sendinclip.errors.category_blocked'));

                    return;
                }

                // Check if the Broadcaster is even registered (deny otherwise)
                // also fetch other data if possible to minimize queries in one go
                // broadcasters should never have enough data to make this a memory issue
                $this->broadcaster = User::query()
                    ->where('id', $this->clipInfo->broadcaster_id)
                    ->whereClipPermission(true)
                    ->with(['broadcasterFilter'])
                    ->first();

                if (! $this->broadcaster instanceof User) {
                    $validator->errors()->add('clip_url', __('sendinclip.errors.broadcaster_not_allowed'));

                    return;
                }

                $userType = $this->user()->getMorphClass();
                $categoryType = new Category()->getMorphClass();

                $groupedFilters = $this->broadcaster->broadcasterFilter->groupBy(['filterable_type', 'state']);
                $this->allowedUsers = $groupedFilters->get($userType)?->get(true)?->pluck('filterable_id')->toArray();
                $this->disallowedUsers = $groupedFilters->get($userType)?->get(false)?->pluck('filterable_id')->toArray();
                $this->allowedCategories = $groupedFilters->get($categoryType)?->get(true)?->pluck('filterable_id')->toArray();
                $this->disallowedCategories = $groupedFilters->get($categoryType)?->get(false)?->pluck('filterable_id')->toArray();

                if (! $this->passesUserChecks()) {
                    $validator->errors()->add('clip_url', __('sendinclip.errors.user_not_allowed_for_broadcaster'));

                    return;
                }

                if (! $this->passesCategoryChecks()) {
                    $validator->errors()->add('clip_url', __('sendinclip.errors.category_blocked'));

                    return;
                }

                // Check if clip already exists
                $clipAlreadyExists = Clip::query()
                    ->where('twitch_id', $this->clipInfo->id)
                    ->exists();

                if ($clipAlreadyExists) {
                    $validator->errors()->add('clip_url', __('sendinclip.errors.clip_already_known'));
                }
            },
        ];
    }

    /**
     * User related checks
     */
    protected function passesUserChecks(): bool
    {
        $user = $this->user();
        $rules = $this->broadcaster->rules ?? [];

        // Check if user is blacklisted
        if (in_array($user->id, $this->disallowedUsers ?? [], true)) {
            return false;
        }

        // bypass if no rules or broadcaster is submitting
        $isAllowed = empty($rules) || $this->broadcaster->id === $user->id;

        // Check if user is in explicit Allow-list (allow if yes)
        if (! $isAllowed &&
            $this->allowedUsers &&
            in_array('userAllowList', $rules, true)
        ) {
            $isAllowed = in_array($user->id, $this->allowedUsers, true);
        }

        // Check if Broadcaster has enabled Mod Allow-list and if the User is on it
        if (! $isAllowed && in_array('userAllowMods', $rules, true)) {
            $isAllowed = $this->twitchService
                ->asUser($user, session()?->get('twitch_access_token'))
                ->isModeratorFor($this->broadcaster);
        }

        // Check if Broadcaster has enabled VIP Allow-list and if the User is on it
        if (! $isAllowed && in_array('userAllowVips', $rules, true)) {
            try {
                $vipInfos = $this->twitchService
                    ->asUser($this->broadcaster)
                    ->onUserTokenRefresh()
                    ->get(TwitchEndpoints::GetVIPs, [
                        'user_id' => $user->id,
                        'broadcaster_id' => $this->broadcaster->id,
                    ]);
                $isAllowed = ! empty($vipInfos['data']);
            } catch (TwitchApiException $th) {
                report($th);

                return false;
            }
        }

        return $isAllowed;
    }

    /**
     * Category related checks
     */
    protected function passesCategoryChecks(): bool
    {
        $gameId = $this->clipInfo->game_id;

        // Check if Broadcaster has banned the Category
        if (in_array($gameId, $this->disallowedCategories ?? [], true)) {
            return false;
        }

        // Check if Broadcaster has enabled Category Whitelist (>0 entries)
        if ($this->allowedCategories && count($this->allowedCategories) > 0) {
            // If whitelist has entries, check if category is whitelisted
            return in_array($gameId, $this->allowedCategories, true);
        }

        return true;
    }
}
