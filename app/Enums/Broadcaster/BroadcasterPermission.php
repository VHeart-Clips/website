<?php

declare(strict_types=1);

namespace App\Enums\Broadcaster;

use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasLabel;

enum BroadcasterPermission: int implements HasLabel
{
    use HasTranslatedLabel;

    case Clips = 0;
    case SubmissionsSetting = 1;
    case CategoryFilter = 2;
    case UserFilter = 3;

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'broadcaster.enums';
    }
}
