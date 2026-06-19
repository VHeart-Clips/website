<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions\Rules;

use App\Models\Broadcaster\Broadcaster;
use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionRule;

readonly class BroadcasterConsentSubmissionRule implements ClipSubmissionRule
{
    public function shouldRun(ClipSubmissionContext $context): bool
    {
        return true;
    }

    public function passes(ClipSubmissionContext $context): bool
    {
        if ($context->isSubmitterBroadcaster()) {
            if (! Broadcaster::query()
                ->where('id', $context->submitter->id)
                ->whereHasConsent()
                ->exists()
            ) {
                session()->flash('showTwitchPermissionsPrompt');
            }

            return true;
        }

        return $context->broadcaster() instanceof Broadcaster;
    }

    public function message(): string
    {
        return __('clips.errors.broadcaster_not_allowed');
    }
}
