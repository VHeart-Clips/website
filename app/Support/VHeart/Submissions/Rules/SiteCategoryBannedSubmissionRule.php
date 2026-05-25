<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions\Rules;

use App\Models\Category;
use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionRule;

readonly class SiteCategoryBannedSubmissionRule implements ClipSubmissionRule
{
    public function shouldRun(ClipSubmissionContext $context): bool
    {
        return true;
    }

    public function passes(ClipSubmissionContext $context): bool
    {

        return ! Category::query()
            ->where('is_banned', true)
            ->where('id', $context->clip()?->gameId)
            ->exists();
    }

    public function message(): string
    {
        return __('clips.errors.category_blocked');
    }
}
