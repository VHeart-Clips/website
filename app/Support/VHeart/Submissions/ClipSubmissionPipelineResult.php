<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions;

readonly class ClipSubmissionPipelineResult
{
    public function __construct(
        public bool $passed,
        public ?string $message = null,
    ) {}

    public static function pass(): self
    {
        return new self(passed: true);
    }

    public static function fail(ClipSubmissionRule $rule): self
    {
        return new self(passed: false, message: $rule->message());
    }
}
