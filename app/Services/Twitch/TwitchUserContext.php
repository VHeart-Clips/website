<?php

declare(strict_types=1);

namespace App\Services\Twitch;

use Closure;
use SensitiveParameter;

readonly class TwitchUserContext
{
    /**
     * @param positive-int $userId
     * @param (Closure(string $accessToken, string $refreshToken, int $expiresIn): void)|null $onRefresh
     */
    public function __construct(
        public int $userId,
        #[SensitiveParameter] public ?string $accessToken,
        #[SensitiveParameter] public string $refreshToken,
        public bool $forceRefresh,
        public ?Closure $onRefresh = null,
    ) {}

    /**
     * Creates a context for the given user.
     *
     * When no $accessToken is provided, forceRefresh is set to true so the
     * client fetches a fresh token before the first request.
     */
    public static function forUser(
        int $userId,
        #[SensitiveParameter] string $refreshToken,
        #[SensitiveParameter] ?string $accessToken = null
    ): self
    {
        return new self(
            userId: $userId,
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            forceRefresh: $accessToken === null,
        );
    }

    /**
     * Called after a successful token refresh.
     */
    public function withTokens(
        #[SensitiveParameter] string $accessToken,
        #[SensitiveParameter] string $refreshToken
    ): self
    {
        return clone ($this, [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'forceRefresh' => false,
        ]);
    }

    /**
     * Returns a new instance with the given refresh callback attached.
     */
    public function withOnRefresh(Closure $callback): self
    {
        return clone ($this, [
            'onRefresh' => $callback,
        ]);
    }
}
