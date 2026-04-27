<?php

declare(strict_types=1);

namespace App\Services\Twitch;

use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use App\Services\Twitch\Contracts\TwitchDtoInterface;
use App\Services\Twitch\Data\CategoryDto;
use App\Services\Twitch\Data\ChannelDto;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\Data\SimpleUserDto;
use App\Services\Twitch\Data\UserDto;
use App\Services\Twitch\Enums\TwitchEndpoints;
use App\Services\Twitch\Exceptions\TwitchApiException;
use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Promises\LazyPromise;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use LogicException;

/**
 * Twitch API service.
 *
 * @example
 *   // App-level token
 *   app(TwitchService::class)->asApp()->getClip('SomeClipId');
 *
 *   // User-token with token refresh callback
 *   app(TwitchService::class)
 *       ->asUser($user, onRefresh: fn($at, $rt) => $user->update([...]))
 *       // if we have a valid session we can use this instead:
 *       ->asSessionUser()
 *       ->getModeratedChannels();
 */
class TwitchService
{
    public function __construct(
        protected TwitchClient $client,
    ) {}

    /**
     * Authenticates as a User.
     * This will automatically update a $user's twitch_refresh_token if $user is an instance of User and you did not provide a custom closure.
     *
     * @param  ?string  $refreshToken
     *                                 The Twitch Refresh Token, if a User or Broadcaster was provided, will be set to the users refresh token if not given.
     * @param  ?string  $accessToken
     *                                The Twitch Access Token, if null we will request a new one based on the $refreshToken
     * @param  (Closure(string $accessToken, string $refreshToken, int $expiresIn): void)|null  $onRefresh
     *                                                                                                      Called automatically when the user token is refreshed.
     */
    public function asUser(User|Broadcaster|int $user, ?string $refreshToken = null, ?string $accessToken = null, ?Closure $onRefresh = null): self
    {
        if ($user instanceof User && ! $refreshToken) {
            $refreshToken = $user->twitch_refresh_token;
        }

        if ($user instanceof Broadcaster && ! $refreshToken) {
            $refreshToken = $user->user->twitch_refresh_token;
            $user = $user->user;
        }

        $context = TwitchUserContext::forUser($user instanceof Model ? $user->id : $user, $refreshToken, $accessToken);

        if ($onRefresh instanceof Closure) {
            $context = $context->withOnRefresh($onRefresh);
        } elseif ($user instanceof User && $refreshToken) {
            $context = $context->withOnRefresh(
                static fn (string $at, string $refreshToken) => $user->update(['twitch_refresh_token' => $refreshToken])
            );
        }

        return clone ($this, [
            'client' => $this->client->asUser($context),
        ]);
    }

    /**
     * Helper for asUser to use the currently authenticated user.
     */
    public function asSessionUser(?Closure $onRefresh = null): self
    {
        $user = auth()->user();

        if (! $user) {
            throw new LogicException('Cannot use TwitchService->asSessionUser() outside of authenticated context.');
        }

        return $this->asUser($user,
            refreshToken: $user->twitch_refresh_token,
            accessToken: session()?->get('twitch_access_token'),
            onRefresh: $onRefresh ?? static function (string $accessToken, string $refreshToken) use ($user): void {
                $user->update(['twitch_refresh_token' => $refreshToken]);
                session()->put('twitch_access_token', $accessToken);
            }
        );
    }

    /**
     * Authenticates as the Application
     */
    public function asApp(): self
    {
        return clone ($this, [
            'client' => $this->client->asApp(),
        ]);
    }

    /**
     * @link https://dev.twitch.tv/docs/api/reference#get-users
     *
     * @return list<UserDto>
     *
     * @throws TwitchApiException|ConnectionException
     */
    public function getUsers(array $params = []): array
    {
        return $this->collection(TwitchEndpoints::GetUsers, $params);
    }

    /**
     * @link https://dev.twitch.tv/docs/api/reference#search-categories
     *
     * @param  positive-int  $first  The maximum number of items to return per page in the response. The minimum page size is 1 item per page and the maximum is 100 items per page. The default is 20.
     * @return list<CategoryDto>
     *
     * @throws TwitchApiException|ConnectionException
     */
    public function searchCategories(string $query, int $first = 20, ?string $after = null): array
    {
        if ($first > 100) {
            throw new InvalidArgumentException('$first must be between 1 and 100.');
        }

        $query = collect(explode(' ', mb_strtolower(mb_trim($query))))->sort()->implode(' ');
        $queryHashed = hash('sha256', $query);

        return Cache::remember(
            "twitch:search:categories:$queryHashed:$first",
            now()->addHour(),
            fn (): array => $this->collection(TwitchEndpoints::SearchCategories, array_filter([
                'query' => $query,
                'after' => $after,
                'first' => $first,
            ]))
        );
    }

    /**
     * @link https://dev.twitch.tv/docs/api/reference#search-channels
     *
     * @param  positive-int  $first  The maximum number of items to return per page in the response. The minimum page size is 1 item per page and the maximum is 100 items per page. The default is 20.
     * @param  bool  $liveOnly  A Boolean value that determines whether the response includes only channels that are currently streaming live. Set to true to get only channels that are streaming live; otherwise, false to get live and offline channels. The default is false.
     * @return list<ChannelDto>
     *
     * @throws TwitchApiException|ConnectionException
     */
    public function searchChannels(string $query, int $first = 20, bool $liveOnly = false, ?string $after = null): array
    {
        if ($first > 100) {
            throw new InvalidArgumentException('$first must be between 1 and 100.');
        }

        return $this->collection(TwitchEndpoints::SearchChannels, array_filter([
            'query' => $query,
            'live_only' => $liveOnly ? 'true' : 'false',
            'after' => $after,
            'first' => $first,
        ]));
    }

    /**
     * Returns a single clip by its Twitch Clip ID, or null if it does not exist.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-clips
     *
     * @return ?ClipDto
     *
     * @throws ConnectionException
     */
    public function getClip(string $clipId): ?TwitchDtoInterface
    {
        try {
            return array_first($this->collection(TwitchEndpoints::GetClips, ['id' => $clipId]));
        } catch (TwitchApiException) {
            return null;
        }
    }

    /**
     * @link https://dev.twitch.tv/docs/api/reference#get-clips
     *
     * @return list<ClipDto>
     *
     * @throws TwitchApiException|ConnectionException
     */
    public function getClips(array $params = []): array
    {
        return $this->collection(TwitchEndpoints::GetClips, $params);
    }

    /**
     * Extracts a Twitch Clip ID from a URL or raw ID string.
     *
     * Returns null for non-clip URLs or empty input.
     *
     * Accepts:
     *   - https://clips.twitch.tv/ClipId-abc123
     *   - https://www.twitch.tv/channel/clip/ClipId-abc123
     *   - https://clips.twitch.tv/embed?clip=ClipId-abc123&parent=x
     *   - ClipId-abc123  (raw ID)
     */
    public function parseClipId(string $input): ?string
    {
        if (preg_match('/([A-Z][a-zA-Z0-9]*-[a-zA-Z0-9_-]+)/', $input, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Returns true if the current user is a moderator for the given broadcaster.
     *
     * **Must** be called with the User in `asUser()` because of API limitations.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-moderated-channels Get Moderated Channels Documentation
     */
    public function isModeratorFor(User|Broadcaster $broadcaster): bool
    {
        return in_array($broadcaster->id, $this->getModeratedChannels(), true);
    }

    /**
     * Returns the list of channels the current user has moderator rights for.
     *
     * **Cached per user for 5 minutes.**
     *
     * @return list<positive-int>
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-moderated-channels Get Moderated Channels Documentation
     */
    public function getModeratedChannels(): array
    {
        $userId = $this->requireUserContext()->userId;

        return Cache::remember(
            "twitch:moderated_channels:{$userId}",
            now()->addMinutes(5),
            fn (): array => array_map(
                static fn (SimpleUserDto $simpleUserDto): int => $simpleUserDto->id,
                $this->collection(TwitchEndpoints::GetModeratedChannels, [
                    'user_id' => $userId,
                    'first' => 100,
                ])
            ),
        );
    }

    /**
     * Returns true if $user is a VIP in the broadcaster's channel.
     *
     * **Must** be called with the Broadcaster in `asUser()` because of API limitations.
     *
     * **Cached per broadcaster <-> user for 1 minute.**
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-vips
     */
    public function isVip(User|Broadcaster $user): bool
    {
        $broadcasterId = $this->requireUserContext()->userId;
        $userId = $user->id;

        return Cache::remember(
            "twitch:vip:{$broadcasterId}:{$userId}",
            now()->addMinute(),
            fn (): bool => count($this->collection(TwitchEndpoints::GetVIPs, [
                'broadcaster_id' => $broadcasterId,
                'user_id' => $userId,
            ])) > 0
        );
    }

    /**
     * GET From Twitch and convert the response to a DTO
     *
     * @template T of TwitchDtoInterface
     *
     * @param  class-string<T>  $dtoClass
     * @return TwitchDtoInterface<T>
     *
     * @throws ConnectionException|TwitchApiException
     */
    public function getAs(string $dtoClass, TwitchEndpoints|string $endpoint, array $params = []): TwitchDtoInterface
    {
        return $dtoClass::from($this->get($endpoint, $params)->json());
    }

    /**
     * GET From Twitch
     *
     * @throws ConnectionException|TwitchApiException
     */
    public function get(TwitchEndpoints|string $endpoint, array $params = []): PromiseInterface|LazyPromise|Response
    {
        return $this->client->get($endpoint instanceof TwitchEndpoints ? $endpoint->value : $endpoint, $params);
    }

    /**
     * POST to Twitch and convert the response to a DTO
     *
     * @template T of TwitchDtoInterface
     *
     * @param  class-string<T>  $dtoClass
     * @return TwitchDtoInterface<T>
     *
     * @throws ConnectionException|TwitchApiException
     */
    public function postAs(string $dtoClass, TwitchEndpoints|string $endpoint, array $data): TwitchDtoInterface
    {
        return $dtoClass::from($this->post($endpoint, $data)->json());
    }

    /**
     * POST to Twitch
     *
     * @throws ConnectionException|TwitchApiException
     */
    public function post(TwitchEndpoints|string $endpoint, array $data): PromiseInterface|LazyPromise|Response
    {
        return $this->client->post($endpoint instanceof TwitchEndpoints ? $endpoint->value : $endpoint, $data);
    }

    /**
     * Fetches a collection endpoint and hydrates the response through its DTO.
     *
     * @return list<TwitchDtoInterface>
     *
     * @throws TwitchApiException|ConnectionException
     */
    public function collection(TwitchEndpoints $endpoint, array $params = []): array
    {
        $dtoClass = $endpoint->dto()
            ?? throw new LogicException("No DTO registered for [{$endpoint->value}].");

        return $dtoClass::fromCollection($this->client->get($endpoint->value, $params)->json());
    }

    /**
     * Fetches an endpoint and returns the raw response data.
     *
     * @throws TwitchApiException|ConnectionException
     */
    public function rawData(TwitchEndpoints $endpoint, array $params = []): array
    {
        return $this->client->get($endpoint->value, $params)->json() ?? [];
    }

    /** @throws LogicException */
    private function requireUserContext(): TwitchUserContext
    {
        return $this->client->userContext()
            ?? throw new LogicException('Call asUser() before accessing user-scoped Twitch endpoints.');
    }
}
