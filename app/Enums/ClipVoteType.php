<?php

declare(strict_types=1);

namespace App\Enums;

enum ClipVoteType: int
{
    case Public = 0;
    case Jury = 1;
}
