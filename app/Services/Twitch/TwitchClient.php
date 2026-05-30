<?php

declare(strict_types=1);

namespace App\Services\Twitch;

use App\Services\Twitch\Contracts\TwitchClientInterface;
use App\Services\Twitch\Exceptions\TwitchApiException;
use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Promises\LazyPromise;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LogicException;

/**
 * HTTP client for the Twitch Helix API.
 */
class TwitchClient implements TwitchClientInterface
{
    private const string BASE_URL = 'https://api.twitch.tv/helix';

    public private(set) bool $isTemplate = true;

    private ?TwitchUserContext $userContext = null;

    public function __construct(
        private readonly string $clientId,
        private readonly TwitchTokenManager $tokens,
    ) {}

    /**
     * Authenticates as the Application
     *
     * Will return a clone of itself to avoid mutation
     */
    public function asApp(): self
    {
        return clone ($this, [
            'userContext' => null,
            'isTemplate' => false,
        ]);
    }

    /**
     * Authenticates as the given User
     *
     * Will return a clone of itself to avoid mutation
     */
    public function asUser(TwitchUserContext $context): self
    {
        return clone ($this, [
            'userContext' => $context,
            'isTemplate' => false,
        ]);
    }

    public function userContext(): ?TwitchUserContext
    {
        return $this->userContext;
    }

    /** @throws TwitchApiException|ConnectionException */
    public function get(string $endpoint, array $params = []): PromiseInterface|LazyPromise|Response
    {
        return $this->request('GET', $endpoint, $params);
    }

    /** @throws TwitchApiException|ConnectionException */
    public function post(string $endpoint, array $data = []): PromiseInterface|LazyPromise|Response
    {
        return $this->request('POST', $endpoint, $data);
    }

    /** @throws TwitchApiException|ConnectionException */
    private function request(string $method, string $endpoint, array $params, bool $retryOn401 = true): PromiseInterface|LazyPromise|Response
    {
        if ($this->isTemplate) {
            throw new LogicException('Call asUser() or asApp() on TwitchClient before making requests.');
        }

        if ($this->userContext?->forceRefresh) {
            $this->doTokenRefresh();
        }

        $token = $this->resolveToken();

        Log::debug("[Twitch Client] $method $endpoint", [
            'type' => $this->userContext instanceof TwitchUserContext ? 'user' : 'app',
            'params' => $params,
        ]);

        /** @var PromiseInterface|LazyPromise|Response $response */
        $response = Http::withHeaders([
            'Client-ID' => $this->clientId,
            'Authorization' => "Bearer $token",
            'Content-Type' => 'application/json',
        ])
            ->{mb_strtolower($method)}(self::BASE_URL.'/'.mb_ltrim($endpoint, '/'), $params);

        // Track App usage only for now
        if (! $this->userContext instanceof TwitchUserContext) {
            TwitchTracker::record($response, $endpoint, $method);
        }

        if ($retryOn401 && $response->status() === 401) {
            $this->handleUnauthorized();

            return $this->request($method, $endpoint, $params, false);
        }

        if ($response->failed()) {
            throw TwitchApiException::requestFailed($response);
        }

        return $response;
    }

    /** @throws TwitchApiException|ConnectionException */
    private function resolveToken(): string
    {
        if ($this->userContext?->accessToken) {
            return $this->userContext->accessToken;
        }

        return $this->tokens->getAppToken();
    }

    /** @throws TwitchApiException|ConnectionException */
    private function handleUnauthorized(): void
    {
        if ($this->userContext instanceof TwitchUserContext) {
            $this->doTokenRefresh();
        } else {
            $this->tokens->invalidateAppToken();
        }
    }

    /** @throws TwitchApiException|ConnectionException */
    private function doTokenRefresh(): void
    {
        if (! $this->userContext?->refreshToken) {
            throw TwitchApiException::userTokenRefreshFailed();
        }

        $data = $this->tokens->refreshUserToken($this->userContext->refreshToken);
        $updated = $this->userContext->withTokens($data['access_token'], $data['refresh_token']);

        if ($updated->onRefresh instanceof Closure) {
            ($updated->onRefresh)($data['access_token'], $data['refresh_token'], $data['expires_in']);
        }

        $this->userContext = $updated;
    }
}
