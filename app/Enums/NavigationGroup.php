<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasLabel;

enum NavigationGroup implements HasLabel
{
    use HasTranslatedLabel;

    case Administration;
    case Moderation;
    case Management;
}
