<?php

namespace App\Enums\Reports;

enum ReportStatus: int // implements HasLabel
{
    // todo: implement translatable label trait
    case Pending = 0;
    case Resolved = 1;
    case Dismissed = 2;
}
