<?php

declare(strict_types=1);

namespace App\Services\Twitch;

use App\Services\Twitch\Exceptions\TwitchApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SensitiveParameter;
use Throwable;

final readonly class TwitchTokenManager
{
    private const string CACHE_KEY = 'twitch:app_access_token';

    private const int TOKEN_EXPIRY_BUFFER_SECONDS = 60;

    public function __construct(
        private string $clientId,
        #[SensitiveParameter] private string $clientSecret,
        private string $authUrl = 'https://id.twitch.tv/oauth2/token',
    ) {}

    /**
     * @throws TwitchApiException|ConnectionException
     */
    public function getAppToken(): string
    {
        if (Cache::has(self::CACHE_KEY)) {
            try {
                return Crypt::decryptString(Cache::get(self::CACHE_KEY));
            } catch (Throwable) {
                Cache::forget(self::CACHE_KEY);
            }
        }

        $response = Http::post($this->authUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        if ($response->failed()) {
            throw TwitchApiException::authenticationFailed($response);
        }

        $data = $response->json();
        $token = $data['access_token'];
        $ttl = $data['expires_in'] - self::TOKEN_EXPIRY_BUFFER_SECONDS;

        Cache::put(self::CACHE_KEY, Crypt::encryptString($token), now()->addSeconds($ttl));

        return $token;
    }

    /**
     * Removes the cached app token, forcing a fresh fetch on the next request.
     *
     * Called after receiving a 401 with an app token.
     */
    public function invalidateAppToken(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     *
     * @throws TwitchApiException|ConnectionException
     */
    public function refreshUserToken(#[SensitiveParameter] string $refreshToken): array
    {
        $response = Http::post($this->authUrl, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->failed()) {
            Log::error('Twitch user token refresh failed', [
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 255),
            ]);

            throw TwitchApiException::userTokenRefreshFailed($response);
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            'expires_in' => $data['expires_in'] ?? 3600,
        ];
    }
}
