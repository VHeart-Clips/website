<?php

declare(strict_types=1);

namespace App\Services\Twitch;

use App\Models\User;
use App\Services\Twitch\Contracts\TwitchDtoInterface;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\Exceptions\TwitchApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TwitchService
{
    protected string $baseUrl = 'https://api.twitch.tv/helix';

    protected string $authUrl = 'https://id.twitch.tv/oauth2/token';

    protected string $clientId;

    protected string $clientSecret;

    protected ?string $userAccessToken = null;

    protected ?string $userRefreshToken = null;

    protected mixed $tokenUpdateCallback = null;

    protected bool $forceUserTokenRefresh = false;

    protected ?User $user = null;

    /**
     * @throws TwitchApiException
     */
    public function __construct()
    {
        $this->clientId = config('services.twitch.client_id', '');
        $this->clientSecret = config('services.twitch.client_secret', '');

        if (($this->clientId === '' || $this->clientId === '0' || ($this->clientSecret === '' || $this->clientSecret === '0')) && app()->environment(['local', 'staging', 'production'])) {
            throw TwitchApiException::ApplicationClientIdOrSecretNotConfiguredError();
        }
    }

    /**
     * Uses specified user access tokens by specific user
     *
     * If not specified, it will request an access token at least once.
     */
    public function asUser(?User $user = null, ?string $access_token = null): self
    {
        $newSelf = clone $this;

        if (! $user instanceof User) {
            $newSelf->user = null;
            $newSelf->userRefreshToken = null;
            $newSelf->userAccessToken = null;
            $newSelf->forceUserTokenRefresh = false;

            return $newSelf;
        }

        $newSelf->user = $user;
        $newSelf->userRefreshToken = $user->twitch_refresh_token;
        $newSelf->userAccessToken = $access_token;

        if ($access_token === null) {
            $newSelf->forceUserTokenRefresh = true;
        }

        return $newSelf;
    }

    /**
     * Uses specified user access tokens from a specific user
     *
     * @link https://dev.twitch.tv/docs/authentication/#user-access-tokens User access tokens
     */
    public function withUserToken(string $accessToken, ?string $refreshToken = null): self
    {
        $this->userAccessToken = $accessToken;
        $this->userRefreshToken = $refreshToken;

        return $this;
    }

    /**
     * Uses specified user access tokens
     *
     * @link https://dev.twitch.tv/docs/authentication/#user-access-tokens User access tokens
     */
    public function onUserTokenRefresh(?callable $onRefresh = null): self
    {
        $this->tokenUpdateCallback = $onRefresh;

        return $this;
    }

    /**
     * Get Prepared headers for accessing the Twitch Api.
     *
     * In case we really need it for requests outside this service for some reason
     *
     * @throws ConnectionException
     *
     * @link https://dev.twitch.tv/docs/authentication/#passing-the-access-token-to-the-api Passing the access token to the API
     */
    public function getHeaders(?string $token = null): array
    {
        return [
            'Client-ID' => $this->clientId,
            'Authorization' => 'Bearer '.(
                $token
                ?? $this->userAccessToken
                ?? $this->getAppAccessToken()
            ),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Returns the App Access token
     *
     * This will request a fresh access token for our application from twitch.
     * We Cache the access token (encrypted) for its lifetime. Validity can only be checked when used.
     *
     * @throws ConnectionException|TwitchApiException
     *
     * @link https://dev.twitch.tv/docs/authentication/#app-access-tokens App access tokens
     */
    public function getAppAccessToken(): string
    {
        if (Cache::has('twitch_access_token')) {
            try {
                return Crypt::decryptString(Cache::get('twitch_access_token'));
            } catch (Throwable) {
            }
        }

        $response = Http::post($this->authUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        if ($response->failed()) {
            throw TwitchApiException::ApplicationAuthenticationError();
        }

        $data = $response->json();
        $token = $data['access_token'];
        $expiresIn = $data['expires_in'];

        Cache::put('twitch_access_token', Crypt::encryptString($token), $expiresIn - 60);

        return $token;
    }

    /**
     * GET From Twitch
     *
     * @throws ConnectionException|TwitchApiException
     */
    public function get(string|TwitchEndpoints $endpoint, array $params = []): array|TwitchDtoInterface
    {
        return $this->request('GET', $endpoint, $params);
    }

    /**
     * POST to Twitch
     *
     * @throws ConnectionException|TwitchApiException
     */
    public function post(string|TwitchEndpoints $endpoint, array $data = []): array|TwitchDtoInterface
    {
        return $this->request('POST', $endpoint, $data);
    }

    /**
     * Returns true if the given user is moderator for the given broadcaster.
     *
     * This will always return false if no user was given.
     */
    public function isModeratorFor(?User $broadCaster = null): bool
    {
        if (! $this->user || ! $broadCaster) {
            return false;
        }

        return in_array($broadCaster->id, array_column($this->getModeratedChannels(), 'broadcaster_id'), false);
    }

    /**
     * Returns the list of Channels the current user has moderator permissions for.
     *
     * @return array<int, int>
     */
    public function getModeratedChannels(): array
    {
        if (! $this->user instanceof User) {
            return [];
        }

        return Cache::remember(sha1('twitch:get:'.TwitchEndpoints::GetModeratedChannels->value.':'.$this->user->id), 300, fn () => $this->get(TwitchEndpoints::GetModeratedChannels, ['user_id' => $this->user->id, 'first' => 100])['data']);
    }

    /**
     * Returns the Clip for this Twitch Clip id if it exists
     */
    public function getClipByID(?string $clipId): ?ClipDto
    {
        try {
            return array_first($this->get(TwitchEndpoints::GetClips, ['id' => $clipId]) ?? []);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Parses the Clip ID from a given Url
     */
    public function parseClipId(string $clipUrl): ?string
    {
        if (preg_match('/([A-Z][a-zA-Z0-9]*-[a-zA-Z0-9_-]+)/', $clipUrl, $m)) {
            return $m[0];
        }

        return null;
    }

    /**
     * @throws ConnectionException|TwitchApiException
     */
    protected function request(
        string $method,
        string|TwitchEndpoints $endpoint,
        array $params = [],
        bool $allowRetry = true
    ): array|TwitchDtoInterface {
        if ($this->forceUserTokenRefresh) {
            $this->tokenRefresh();
        }

        $dataTransferObject = null;

        if ($endpoint instanceof TwitchEndpoints) {
            $dataTransferObject = $endpoint->getDataTransferObject();
        } else {
            $dataTransferObject = TwitchEndpoints::tryFrom($endpoint)?->getDataTransferObject();
        }

        if (! is_string($endpoint)) {
            $endpoint = $endpoint->value;
        }

        Log::debug("[Twitch Service] {$method} {$endpoint}",
            ['isUserToken' => (bool) $this->userAccessToken, 'params' => $params]);

        $client = Http::withHeaders($this->getHeaders());

        $url = $this->baseUrl.'/'.mb_ltrim($endpoint, '/');

        $response = mb_strtoupper($method) === 'GET' ? $client->get($url, $params) : $client->post($url, $params);

        if ($allowRetry && $response->status() === 401 && $this->tokenRefresh()) {
            return $this->request($method, $endpoint, $params, false);
        }

        if ($response->failed()) {
            throw TwitchApiException::GenericApiResponseError($response);
        }

        if ($dataTransferObject) {
            return $dataTransferObject::fromArray($response->json());
        }

        return $response->json();
    }

    /**
     * @throws ConnectionException
     */
    protected function tokenRefresh(): bool
    {
        if ($this->userAccessToken || $this->forceUserTokenRefresh) {
            if (! $this->userRefreshToken) {
                return false;
            }

            $response = Http::post($this->authUrl, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->userRefreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->userAccessToken = $data['access_token'];
                $this->userRefreshToken = $data['refresh_token'] ?? $this->userRefreshToken;
                $expiresIn = $data['expires_in'] ?? 3600;

                if (is_callable($this->tokenUpdateCallback)) {
                    // notify caller about that one
                    call_user_func($this->tokenUpdateCallback, $this->userAccessToken, $this->userRefreshToken,
                        $expiresIn);
                }

                return true;
            }

            Log::error('Failed to refresh user token', [
                'response' => $response->status(),
                'body' => Str::limit($response->body(), 255),
            ]);

            return false;
        }

        // We forget the cache key so the next getAccessToken() call fetches a fresh one.
        Cache::forget('twitch_access_token');

        return true;
    }
}
