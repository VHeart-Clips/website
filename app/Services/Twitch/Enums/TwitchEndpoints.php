<?php

declare(strict_types=1);

namespace App\Services\Twitch\Enums;

use App\Services\Twitch\Contracts\TwitchDtoInterface;
use App\Services\Twitch\Data\CategoryDto;
use App\Services\Twitch\Data\ChannelDto;
use App\Services\Twitch\Data\ClipDownloadDto;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\Data\GameDto;
use App\Services\Twitch\Data\SimpleUserDto;
use App\Services\Twitch\Data\UserDto;

enum TwitchEndpoints: string
{
    /**
     * Gets information about one or more channels.
     *
     * - Requires app or user access token.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-channel-information
     */
    case GetChannelInformation = 'channels';

    /**
     * Gets one or more video clips that were captured from streams. For information about clips, see How to use clips.
     *
     * When using pagination for clips, note that the maximum number of results returned over multiple requests will be approximately 1,000. If additional results are necessary, paginate over different query parameters such as multiple started_at and ended_at timeframes to refine the search.
     *
     * - Requires app or user access token.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-clips
     */
    case GetClips = 'clips';

    /**
     * Provides URLs to download the video file(s) for the specified clips. For information about clips
     *
     * - Requires **user access token** with **editor:manage:clips** or **channel:manage:clips** scope.
     * - Rate limited to 100 requests/minute
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-clips-download
     */
    case GetClipsDownload = 'clips/downloads';

    /**
     * Gets information about specified categories or games.
     *
     * You may get up to 100 categories or games by specifying their ID or name. You may specify all IDs, all names, or a combination of IDs and names. If you specify a combination of IDs and names, the total number of IDs and names must not exceed 100.
     *
     * - Requires app or user access token.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-games
     */
    case GetGames = 'games';

    /**
     * Gets all users that the broadcaster banned or put in a timeout.
     *
     * - Requires **user access token** with **moderation:read** or **moderator:manage:banned_users** scope.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-banned-users
     */
    case GetBannedUsers = 'moderation/banned';

    /**
     * Gets a list of channels that the specified user has moderator privileges in.
     *
     * - Requires **user access token** with **user:read:moderated_channels** scope.
     *  - Query parameter user_id must match the user ID in the **user access token**
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-moderated-channels
     */
    case GetModeratedChannels = 'moderation/channels';

    /**
     * Gets all users allowed to moderate the broadcaster’s chat room.
     *
     * - Requires **user access token** with **moderation:read** scope.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-moderators
     */
    case GetModerators = 'moderation/moderators';

    /**
     * Gets a list of the broadcaster’s VIPs.
     *
     * - Requires **user access token** with **channel:read:vips** scope.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-vips
     */
    case GetVIPs = 'channels/vips';

    /**
     * Gets the games or categories that match the specified query.
     *
     * To match, the category’s name must contain all parts of the query string. For example, if the query string is 42, the response includes any category name that contains 42 in the title. If the query string is a phrase like love computer, the response includes any category name that contains the words love and computer anywhere in the name. The comparison is case insensitive.
     *
     * - Requires app or user access token.
     *
     * @link https://dev.twitch.tv/docs/api/reference#search-categories
     */
    case SearchCategories = 'search/categories';

    /**
     * Gets the channels that match the specified query and have streamed content within the past 6 months.
     *
     * - Requires an app access token or user access token.
     *
     * @link https://dev.twitch.tv/docs/api/reference#search-channels
     */
    case SearchChannels = 'search/channels';

    /**
     * Gets a list of all streams.
     *
     * The list is in descending order by the number of viewers watching the stream.
     * Because viewers come and go during a stream, it’s possible to find duplicate or missing streams in the list as you page through the results.
     *
     * - Requires app or user access token
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-streams
     */
    case GetStreams = 'streams';

    /**
     * Gets information about one or more users.
     *
     * You may look up users using their user ID, login name, or both but the sum total of the number of users you may look up is 100. For example, you may specify 50 IDs and 50 names or 100 IDs or names, but you cannot specify 100 IDs and 100 names.
     *
     * If you don’t specify IDs or login names, the request returns information about the user in the access token if you specify a user access token.
     *
     * To include the user’s verified email address in the response, you must use a user access token that includes the `user:read:email` scope.
     *
     * - Requires app or user access token
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-users
     */
    case GetUsers = 'users';

    /** @return class-string<TwitchDtoInterface>|null */
    public function dto(): ?string
    {
        return match ($this) {
            self::GetClips => ClipDto::class,
            self::SearchCategories => CategoryDto::class,
            self::GetGames => GameDto::class,
            self::GetClipsDownload => ClipDownloadDto::class,
            self::SearchChannels => ChannelDto::class,
            self::GetUsers => UserDto::class,
            self::GetModeratedChannels, self::GetVIPs => SimpleUserDto::class,
            default => null,
        };
    }
}
