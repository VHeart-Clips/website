<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions\Rules;

use App\Enums\Permission;
use App\Models\Clip;
use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionRule;

readonly class TotalDailyLimitSubmissionRule implements ClipSubmissionRule
{
    public function shouldRun(ClipSubmissionContext $context): bool
    {
        return (bool) config('vheart.clips.submission.limits.total', false);
    }

    public function passes(ClipSubmissionContext $context): bool
    {
        if ($context->submitter->can(Permission::CanIgnoreTotalSubmissionLimits)) {
            return true;
        }

        return Clip::query()
            ->withTrashed()
            ->whereSubmittedAfter(now()->startOfDay())
            ->whereSubmitterId($context->submitter->id)
            ->count() < config('vheart.clips.submission.limits.total');
    }

    public function message(): string
    {
        return __('clips.errors.total_limit_reached');
    }
}
