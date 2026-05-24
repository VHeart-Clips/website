<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions\Rules;

use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use App\Services\Twitch\TwitchService;
use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionRule;

readonly class BroadcasterUserSubmissionRule implements ClipSubmissionRule
{
    public function __construct(
        private TwitchService $twitchService
    ) {}

    public function shouldRun(ClipSubmissionContext $context): bool
    {
        return ! $context->isSubmitterBroadcaster();
    }

    public function passes(ClipSubmissionContext $context): bool
    {
        $broadcaster = $context->broadcaster();
        $userId = $context->submitter->id;

        if ($this->hasDisallowedUser($context->broadcaster(), $userId)) {
            return false;
        }

        if ($this->hasAllowedUser($context->broadcaster(), $userId)) {
            return true;
        }

        if (! $this->hasEmptyUserAllowlist($context->broadcaster())) {
            return false;
        }

        if ($broadcaster->submit_user_allowed) {
            return true;
        }

        return $broadcaster->submit_mods_allowed
        && $this->twitchService->asSessionUser()->isModeratorFor($broadcaster->user);
    }

    public function message(): string
    {
        return __('clips.errors.user_not_allowed_for_broadcaster');
    }

    private function hasAllowedUser(?Broadcaster $broadcaster, int $userId): bool
    {
        return $this->hasFilteredUser($broadcaster, $userId, true);
    }

    private function hasDisallowedUser(?Broadcaster $broadcaster, int $userId): bool
    {
        return $this->hasFilteredUser($broadcaster, $userId, false);
    }

    private function hasFilteredUser(?Broadcaster $broadcaster, int $userId, bool $filterState): bool
    {
        return $broadcaster
            ?->filters()
            ->where('filterable_type', new User()->getMorphClass())
            ->where('filterable_id', $userId)
            ->where('state', $filterState)
            ->exists();
    }

    private function hasEmptyUserAllowlist(?Broadcaster $broadcaster): bool
    {
        return $broadcaster
            ?->filters()
            ->where('filterable_type', new User()->getMorphClass())
            ->where('state', true)
            ->doesntExist();
    }
}
