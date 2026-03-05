<?php

declare(strict_types=1);

namespace App\Enums\Broadcaster;

use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasLabel;

enum BroadcasterConsent: int implements HasLabel
{
    use HasTranslatedLabel;

    case Compilations = 0;
    case Shorts = 1;
}
