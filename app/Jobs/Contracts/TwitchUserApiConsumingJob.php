<?php

declare(strict_types=1);

namespace App\Jobs\Contracts;

/**
 * Jobs that use the Twitch API with user tokens must use this Contract and the `twitch-api-user` rate limit
 *
 * @link https://dev.twitch.tv/docs/api/guide#twitch-rate-limits
 */
interface TwitchUserApiConsumingJob
{
    public function getTwitchUserIdentifier(): int;
}
