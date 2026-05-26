<?php

declare(strict_types=1);

namespace App\Services\Twitch\Enums;

use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasLabel;

enum TwitchStreamType: string implements HasLabel
{
    use HasTranslatedLabel;

    case Error = ''; // I hate twitch for that
    case Live = 'live';
    case All = 'all';
}
