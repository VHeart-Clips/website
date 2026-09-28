<?php

declare(strict_types=1);

namespace App\Enums\Reports;

use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasLabel;

/**
 * @deprecated Replaced by Report Categories, will be removed in the future
 */
enum ReportReason: int implements HasLabel
{
    use HasTranslatedLabel;

    case Other = 0;
    case Spam = 1;
    case Harassment = 2;
    case HateSpeech = 3;
    case AiContent = 4;
    case ContentUnavailable = 5;

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'reports.enums';
    }
}
