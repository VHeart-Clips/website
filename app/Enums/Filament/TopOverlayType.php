<?php

declare(strict_types=1);

namespace App\Enums\Filament;

use App\Queries\Leaderboard\UserLeaderboardSubmitterQuery;
use App\Queries\Leaderboard\UserLeaderboardVoteQuery;

enum TopOverlayType: string
{
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
