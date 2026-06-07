<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions\Rules;

use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionRule;

/**
 * Denies Submission if the Broadcaster or their related User has an active ban.
 */
readonly class BroadcasterNotBannedRule implements ClipSubmissionRule
{
    public function shouldRun(ClipSubmissionContext $context): bool
    {
        return true;
    }

    public function passes(ClipSubmissionContext $context): bool
    {
        if (! $context->broadcaster()) {
            return false;
        }

        return ! (
            $context->broadcaster()->isBanned()
            || $context->broadcaster()->user?->isBanned()
        );
    }

    public function message(): string
    {
        return __('clips.errors.broadcaster_not_allowed');
    }
}
