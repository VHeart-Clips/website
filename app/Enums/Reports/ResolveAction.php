<?php

declare(strict_types=1);

namespace App\Enums\Reports;

use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasLabel;

enum ResolveAction: int implements HasLabel
{
    use HasTranslatedLabel;

    case Other = 0;
    case Dismissed = 1;
    case ContentRemoved = 2;
    case ContentEdited = 3;
    case UserBanned = 4;

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'reports.enums';
    }
}
