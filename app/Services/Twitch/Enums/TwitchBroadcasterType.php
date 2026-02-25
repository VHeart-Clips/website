<?php

declare(strict_types=1);

namespace App\Services\Twitch\Enums;

enum TwitchBroadcasterType: string
{
    case Normal = '';  // I hate twitch for that
    case Affiliate = 'affiliate';
    case Partner = 'partner';
}
