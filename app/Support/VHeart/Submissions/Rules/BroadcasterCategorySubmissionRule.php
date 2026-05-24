<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions\Rules;

use App\Models\Broadcaster\Broadcaster;
use App\Models\Category;
use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionRule;

readonly class BroadcasterCategorySubmissionRule implements ClipSubmissionRule
{
    public function shouldRun(ClipSubmissionContext $context): bool
    {
        return ! $context->isSubmitterBroadcaster();
    }

    public function passes(ClipSubmissionContext $context): bool
    {
        $gameId = $context->clip()?->gameId;

        if ($this->hasDisallowedCategory($context->broadcaster(), $gameId)) {
            return false;
        }
        if ($this->hasEmptyCategoryAllowlist($context->broadcaster())) {
            return true;
        }
        return $this->hasAllowedCategory($context->broadcaster(), $gameId);
    }

    public function message(): string
    {
        return __('clips.errors.category_blocked');
    }

    private function hasAllowedCategory(?Broadcaster $broadcaster, int $categoryId): bool
    {
        return $this->hasFilteredCategory($broadcaster, $categoryId, true);
    }

    private function hasDisallowedCategory(?Broadcaster $broadcaster, int $categoryId): bool
    {
        return $this->hasFilteredCategory($broadcaster, $categoryId, false);
    }

    private function hasFilteredCategory(?Broadcaster $broadcaster, int $categoryId, bool $filterState): bool
    {
        return $broadcaster
            ?->filters()
            ->where('filterable_type', new Category()->getMorphClass())
            ->where('filterable_id', $categoryId)
            ->where('state', $filterState)
            ->exists();
    }

    private function hasEmptyCategoryAllowlist(?Broadcaster $broadcaster): bool
    {
        return $broadcaster
            ?->filters()
            ->where('filterable_type', new Category()->getMorphClass())
            ->where('state', true)
            ->doesntExist();
    }
}
