<?php

declare(strict_types=1);

namespace App\Enums\Reports;

enum ReportReason: int // implements HasLabel
{
    // todo: implement translatable label trait
    case Other = 0;
    case Spam = 1;
    case Harassment = 2;
    case HateSpeech = 3;
}
