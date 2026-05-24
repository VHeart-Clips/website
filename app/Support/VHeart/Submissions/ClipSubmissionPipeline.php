<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions;

use App\Services\Twitch\TwitchService;
use App\Support\VHeart\Submissions\Rules\BroadcasterCategorySubmissionRule;
use App\Support\VHeart\Submissions\Rules\BroadcasterConsentSubmissionRule;
use App\Support\VHeart\Submissions\Rules\BroadcasterDailyLimitSubmissionRule;
use App\Support\VHeart\Submissions\Rules\BroadcasterUserSubmissionRule;
use App\Support\VHeart\Submissions\Rules\ClipNotDuplicateSubmissionRule;
use App\Support\VHeart\Submissions\Rules\MaximumAgeSubmissionRule;
use App\Support\VHeart\Submissions\Rules\MinimumLengthSubmissionRule;
use App\Support\VHeart\Submissions\Rules\SiteCategoryBannedSubmissionRule;
use App\Support\VHeart\Submissions\Rules\TotalDailyLimitSubmissionRule;
use Illuminate\Support\Arr;

readonly class ClipSubmissionPipeline
{
    /** @param ClipSubmissionRule[] $rules */
    public function __construct(
        private array $rules
    ) {}

    public static function make(TwitchService $twitchService): static
    {
        return new static([
            new ClipNotDuplicateSubmissionRule(),
            new TotalDailyLimitSubmissionRule(),
            new BroadcasterDailyLimitSubmissionRule(),

            new MinimumLengthSubmissionRule(),
            new MaximumAgeSubmissionRule(),
            new SiteCategoryBannedSubmissionRule(),

            new BroadcasterConsentSubmissionRule(),
            new BroadcasterUserSubmissionRule($twitchService),
            new BroadcasterCategorySubmissionRule(),
        ]);
    }

    /**
     * @param  class-string<ClipSubmissionRule>|array<class-string<ClipSubmissionRule>>  $rules  Rules to remove based on a condition
     * @return $this
     */
    public function withoutIf(string|array $rules, bool $condition): static
    {
        if (! $condition) {
            return $this;
        }

        return new static(
            array_values(
                array_filter($this->rules, static fn ($r) => ! in_array($r::class, Arr::wrap($rules), true))
            )
        );
    }

    /**
     * @param  ClipSubmissionRule|ClipSubmissionRule[]  $rules  Rules to add based on a condition
     * @return $this
     */
    public function withIf(ClipSubmissionRule|array $rules, bool $condition = true): static
    {
        if (! $condition) {
            return $this;
        }

        return new static(
            array_values([...$this->rules, ...Arr::wrap($rules)])
        );
    }

    public function check(ClipSubmissionContext $context): ClipSubmissionPipelineResult
    {
        foreach ($this->rules as $rule) {
            if (! $rule->shouldRun($context)) {
                continue;
            }

            if (! $rule->passes($context)) {
                return ClipSubmissionPipelineResult::fail($rule);
            }
        }

        return ClipSubmissionPipelineResult::pass();
    }
}
