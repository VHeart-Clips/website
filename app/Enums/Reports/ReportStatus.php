<?php

declare(strict_types=1);

namespace App\Enums\Reports;

use Filament\Support\Contracts\HasLabel;

enum ReportStatus: int implements HasLabel
{
    case Pending = 0;
    case Resolved = 1;
    case Dismissed = 2;

    public function getLabel(): string
    {
        // todo: implement translatable label trait
        return $this->name;
    }
}
