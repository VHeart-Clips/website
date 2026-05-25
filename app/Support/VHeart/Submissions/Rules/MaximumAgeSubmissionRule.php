<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions\Rules;

use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionRule;

readonly class MaximumAgeSubmissionRule implements ClipSubmissionRule
{
    public function shouldRun(ClipSubmissionContext $context): bool
    {
        return (bool) config('vheart.clips.submission.maximum_age', false);
    }

    public function passes(ClipSubmissionContext $context): bool
    {
        return ! $context->clip()?->createdAt
            ->add(config('vheart.clips.submission.maximum_age'))
            ->isPast();
    }

    public function message(): string
    {
        return __('clips.errors.too_old', [
            'age' => config('vheart.clips.submission.maximum_age')->forHumans(),
        ]);
    }
}
