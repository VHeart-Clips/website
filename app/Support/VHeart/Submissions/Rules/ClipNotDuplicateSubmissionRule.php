<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions\Rules;

use App\Models\Clip;
use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionRule;

readonly class ClipNotDuplicateSubmissionRule implements ClipSubmissionRule
{
    public function shouldRun(ClipSubmissionContext $context): bool
    {
        return true;
    }

    public function passes(ClipSubmissionContext $context): bool
    {
        return Clip::query()
            ->withTrashed()
            ->where('twitch_id', $context->clipId)
            ->doesntExist();
    }

    public function message(): string
    {
        return __('clips.errors.clip_already_known');
    }
}
