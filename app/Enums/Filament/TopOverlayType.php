<?php

declare(strict_types=1);

namespace App\Enums\Filament;

use App\Enums\Traits\HasHeadlineLabel;
use App\Queries\Leaderboard\UserLeaderboardSubmitterQuery;
use App\Queries\Leaderboard\UserLeaderboardVoteQuery;
use Filament\Support\Contracts\HasLabel;

enum TopOverlayType: string implements HasLabel
{
    use HasHeadlineLabel;

    case Votes = 'votes';
    case Submits = 'submits';

    public function getQueryClass(): string
    {
        return match ($this) {
            self::Votes => UserLeaderboardVoteQuery::class,
            self::Submits => UserLeaderboardSubmitterQuery::class
        };
    }
}
