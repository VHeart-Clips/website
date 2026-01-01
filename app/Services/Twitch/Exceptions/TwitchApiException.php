<?php

namespace App\Services\Twitch\Exceptions;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Client\Response as IlluminateResponse;
use Illuminate\Http\Client\Promises\LazyPromise;

class TwitchApiException extends Exception
{
    public static function GenericApiResponseError(PromiseInterface|LazyPromise|GuzzleResponse|IlluminateResponse $response): TwitchApiException
    {
        return new self("Twitch API Error: {$response->getReasonPhrase()} ({$response->getStatusCode()})");
    }

    public static function ApplicationAuthenticationError(): TwitchApiException
    {
        return new self("Could not authenticate app with Twitch");
    }

    public static function ApplicationClientIdOrSecretNotConfiguredError(): TwitchApiException
    {
        return new self("Twitch Client ID or Secret is not configured");
    }
}
