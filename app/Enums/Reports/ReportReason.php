<?php

declare(strict_types=1);

namespace App\Enums\Reports;

use Filament\Support\Contracts\HasLabel;

enum ReportReason: int implements HasLabel
{
    case Other = 0;
    case Spam = 1;
    case Harassment = 2;
    case HateSpeech = 3;

    public function getLabel(): string
    {
        // todo: implement translatable label trait
        return $this->name;
    }
}
