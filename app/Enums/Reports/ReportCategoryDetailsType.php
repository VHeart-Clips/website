<?php

declare(strict_types=1);

namespace App\Enums\Reports;

use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasLabel;

enum ReportCategoryDetailsType: string implements HasLabel
{
    use HasTranslatedLabel;

    case Disabled = 'disabled';
    case Optional = 'optional';
    case Required = 'required';

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'reports.enums';
    }
}
