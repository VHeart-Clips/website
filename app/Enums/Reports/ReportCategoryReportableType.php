<?php

declare(strict_types=1);

namespace App\Enums\Reports;

use App\Enums\Traits\HasHeadlineLabel;
use Filament\Support\Contracts\HasLabel;

enum ReportCategoryReportableType: string implements HasLabel
{
    use HasHeadlineLabel;

    case User = 'user';
    case Broadcaster = 'broadcaster';
    case Clip = 'clip';
}
