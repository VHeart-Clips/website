<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions\Rules;

use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionRule;

readonly class MinimumLengthSubmissionRule implements ClipSubmissionRule
{
    public function shouldRun(ClipSubmissionContext $context): bool
    {
        return (bool) config('vheart.clips.submission.minimum_length', false);
    }

    public function passes(ClipSubmissionContext $context): bool
    {
        return $context->clip()?->duration >= config('vheart.clips.submission.minimum_length');
    }

    public function message(): string
    {
        return __('clips.errors.too_short', [
            'seconds' => config('vheart.clips.submission.minimum_length'),
        ]);
    }
}
