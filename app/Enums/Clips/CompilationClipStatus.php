<?php

declare(strict_types=1);

namespace App\Enums\Clips;

use App\Enums\Traits\HasHeadlineLabel;
use Filament\Support\Contracts\HasLabel;

enum CompilationClipStatus: int implements HasLabel
{
    use HasHeadlineLabel;

    case Pending = 0;
    case InProgress = 1;
    case Completed = 2;
}
