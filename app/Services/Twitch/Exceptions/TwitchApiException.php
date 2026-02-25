<?php

declare(strict_types=1);

namespace App\Services\Twitch\Exceptions;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Client\Promises\LazyPromise;
use Illuminate\Http\Client\Response as IlluminateResponse;

class TwitchApiException extends Exception
{
    public static function GenericApiResponseError(PromiseInterface|LazyPromise|GuzzleResponse|IlluminateResponse $response): self
    {
        return new self("Twitch API Error: {$response->getReasonPhrase()} ({$response->getStatusCode()})");
    }

    public static function ApplicationAuthenticationError(): self
    {
        return new self('Could not authenticate app with Twitch');
    }

    public static function ApplicationClientIdOrSecretNotConfiguredError(): self
    {
        return new self('Twitch Client ID or Secret is not configured');
    }
}
