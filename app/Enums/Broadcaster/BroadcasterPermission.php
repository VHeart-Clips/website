<?php

declare(strict_types=1);

namespace App\Enums\Broadcaster;

use App\Enums\Traits\HasTranslatedDescription;
use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum BroadcasterPermission: int implements HasDescription, HasLabel
{
    use HasTranslatedDescription;
    use HasTranslatedLabel;

    case Clips = 0;
    case SubmissionsSetting = 1;
    case CategoryFilter = 2;
    case UserFilter = 3;
    case RemovalRequests = 4;

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'broadcaster.enums';
    }

    private function getTranslatableEnumDescriptionPrefix(): string
    {
        return 'broadcaster.enums';
    }
}
