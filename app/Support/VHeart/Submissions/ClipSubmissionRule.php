<?php

declare(strict_types=1);

namespace App\Support\VHeart\Submissions;

interface ClipSubmissionRule
{
    public function shouldRun(ClipSubmissionContext $context): bool;

    public function passes(ClipSubmissionContext $context): bool;

    public function message(): string;
}
