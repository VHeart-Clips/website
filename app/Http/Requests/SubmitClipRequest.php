<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Category;
use App\Models\Clip;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\TwitchService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SubmitClipRequest extends FormRequest
{
    public ?ClipDto $clipInfo = null;

    public ?Broadcaster $broadcaster = null;

    public ?array $disallowedUsers = null;

    public ?array $allowedUsers = null;

    public ?array $disallowedCategories = null;

    public ?array $allowedCategories = null;

    public ?string $clipId = null;

    public function __construct(
        protected TwitchService $twitchService,
    ) {
        parent::__construct();
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
            'parsed_clip_id.required' => __('clips.errors.clip_not_found'),
            'parsed_clip_id.unique' => __('clips.errors.clip_already_known'),
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
                    $validator->errors()->add('clip_url', __('clips.errors.clip_not_found'));

                    return;
                }

                if (
                    ($totalLimit = config('vheart.clips.submission.limits.total', false))
                    && $this->user()->cannot(Permission::CanIgnoreTotalSubmissionLimits)
                ) {
                    $total = Clip::query()
                        ->withTrashed()
                        ->whereSubmittedAfter(now()->startOfDay())
                        ->whereSubmitterId($this->user()->id)
                        ->count();

                    if ($total >= $totalLimit) {
                        $validator->errors()->add('clip_url', __('clips.errors.total_limit_reached'));

                        return;
                    }
                }

                $this->clipInfo = $this->twitchService
                    ->asSessionUser()
                    ->getClip($this->clipId);

                if (! $this->clipInfo instanceof ClipDto) {
                    $validator->errors()->add('clip_url', __('clips.errors.clip_not_found'));

                    return;
                }

                // Check Limitations
                if (
                    ($broadcasterLimit = config('vheart.clips.submission.limits.per_broadcaster', false))
                    && $this->user()->cannot(Permission::CanIgnoreBroadcasterSubmissionLimits)
                ) {
                    $total = Clip::query()
                        ->withTrashed()
                        ->whereSubmittedAfter(now()->startOfDay())
                        ->whereBroadcastBy($this->clipInfo->broadcasterId)
                        ->whereSubmitterId($this->user()->id)
                        ->count();

                    if ($total >= $broadcasterLimit) {
                        $validator->errors()->add('clip_url', __('clips.errors.broadcaster_limit_reached'));

                        return;
                    }
                }

                if ($this->clipInfo->duration < config('vheart.clips.submission.minimum_length')) {
                    $validator->errors()->add('clip_url', __('clips.errors.too_short', [
                        'seconds' => config('vheart.clips.submission.minimum_length'),
                    ]));
                }

                if ($this->clipInfo->createdAt->add(config('vheart.clips.submission.maximum_age'))->isPast()) {
                    $validator->errors()->add('clip_url', __('clips.errors.too_old', [
                        'age' => config('vheart.clips.submission.maximum_age')->forHumans(),
                    ]));
                }

                // Check if the Category is Site-Banned
                $isCategoryBanned = Category::query()
                    ->where('is_banned', true)
                    ->where('id', $this->clipInfo->gameId)
                    ->exists();

                if ($isCategoryBanned) {
                    $validator->errors()->add('clip_url', __('clips.errors.category_blocked'));

                    return;
                }

                // Broadcaster can always bypass their own rules
                // We only bypass submit restrictions here though, consent is still required to see and use them
                if ($this->clipInfo->broadcasterId === $this->user()->id) {
                    Log::debug('Bypassing submission restrictions, broadcaster is submitting their own clip.', [
                        'clip_id' => $this->clipInfo->id,
                        'broadcaster_id' => $this->clipInfo->broadcasterId,
                    ]);

                    if (! Broadcaster::query()
                        ->where('id', $this->user()->id)
                        ->whereGaveConsent()
                        ->exists()
                    ) {
                        session()->flash('showTwitchPermissionsPrompt');
                    }
                } else {
                    // Check if the Broadcaster is even registered (deny otherwise)
                    // also fetch other data if possible to minimize queries in one go
                    // broadcasters should never have enough data to make this a memory issue
                    $this->broadcaster = Broadcaster::query()
                        ->where('id', $this->clipInfo->broadcasterId)
                        ->whereGaveConsent()
                        ->with(['filters'])
                        ->first();

                    if (! $this->broadcaster instanceof Broadcaster) {
                        $validator->errors()->add('clip_url', __('clips.errors.broadcaster_not_allowed'));

                        return;
                    }

                    $userType = $this->user()->getMorphClass();
                    $categoryType = new Category()->getMorphClass();

                    $groupedFilters = $this->broadcaster->filters->groupBy(['filterable_type', 'state']);
                    $this->allowedUsers = $groupedFilters->get($userType)?->get(true)?->pluck('filterable_id')->toArray();
                    $this->disallowedUsers = $groupedFilters->get($userType)?->get(false)?->pluck('filterable_id')->toArray();
                    $this->allowedCategories = $groupedFilters->get($categoryType)?->get(true)?->pluck('filterable_id')->toArray();
                    $this->disallowedCategories = $groupedFilters->get($categoryType)?->get(false)?->pluck('filterable_id')->toArray();

                    if (! $this->passesUserChecks()) {
                        $validator->errors()->add('clip_url', __('clips.errors.user_not_allowed_for_broadcaster'));

                        return;
                    }

                    if (! $this->passesCategoryChecks()) {
                        $validator->errors()->add('clip_url', __('clips.errors.category_blocked'));

                        return;
                    }
                }

                // Check if clip already exists
                $clipAlreadyExists = Clip::query()
                    ->withTrashed()
                    ->where('twitch_id', $this->clipInfo->id)
                    ->exists();

                if ($clipAlreadyExists) {
                    $validator->errors()->add('clip_url', __('clips.errors.clip_already_known'));
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
        $broadcaster = $this->broadcaster;
        $weAreBroadcaster = $broadcaster->id === $user->id;

        // Check if user is blacklisted
        if (in_array($user->id, $this->disallowedUsers ?? [], true)) {
            return false;
        }

        // bypass if broadcaster is allowing everyone or is submitting themselves
        $isAllowed = $broadcaster->submit_user_allowed || $weAreBroadcaster;

        if ($isAllowed) {
            return true;
        }

        // Check if user is in explicit Allow-list (allow if yes)
        if ($this->allowedUsers) {
            $isAllowed = in_array($user->id, $this->allowedUsers, true);
        }

        // Check if Broadcaster has allowed moderators to submit
        if (! $isAllowed && $broadcaster->submit_mods_allowed) {
            return $this->twitchService
                ->asSessionUser()
                ->isModeratorFor($broadcaster);
        }

        /**
         * @see https://github.com/VHeart-Clips/website/issues/714
         */
        /*
        // Check if Broadcaster has allowed VIPs to submit
        if (! $isAllowed && $broadcaster->submit_vip_allowed) {
            try {
                $isAllowed = $this->twitchService
                    ->asUser($broadcaster)
                    ->isVip($user);
            } catch (TwitchApiException $th) {
                report($th);

                return false;
            }
        }
        */

        return $isAllowed;
    }

    /**
     * Category related checks
     */
    protected function passesCategoryChecks(): bool
    {
        $gameId = $this->clipInfo->gameId;

        // Check if Broadcaster has banned the Category
        if (in_array($gameId, $this->disallowedCategories ?? [], true)) {
            return false;
        }

        // Check if Broadcaster has enabled Category Whitelist (>0 entries)
        if ($this->allowedCategories) {
            // If whitelist has entries, check if category is whitelisted
            return in_array($gameId, $this->allowedCategories, true);
        }

        return true;
    }
}
