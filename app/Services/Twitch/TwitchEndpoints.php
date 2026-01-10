<?php

declare(strict_types=1);

namespace App\Services\Twitch;

use App\Services\Twitch\Data\CategoryDto;
use App\Services\Twitch\Data\ClipDownloadDto;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\Data\GameDto;
use App\Services\Twitch\Data\TwitchDtoInterface;

enum TwitchEndpoints: string
{
    /**
     * Gets information about one or more channels.
     *
     * Requires an app access token or user access token.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-channel-information
     */
    case GetChannelInformation = 'channels';

    /**
     * Gets one or more video clips that were captured from streams. For information about clips, see How to use clips.
     *
     * When using pagination for clips, note that the maximum number of results returned over multiple requests will be approximately 1,000. If additional results are necessary, paginate over different query parameters such as multiple started_at and ended_at timeframes to refine the search.
     *
     * Requires an **app access token** or **user access token.**
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-clips
     */
    case GetClips = 'clips';

    /**
     * Provides URLs to download the video file(s) for the specified clips. For information about clips
     *
     * Limited to 100 requests per minute.
     *
     * **Requires an app access token or user access token that includes the editor:manage:clips or channel:manage:clips scope.**
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-clips-download
     */
    case GetClipsDownload = 'clips/download';

    /**
     * Gets information about specified categories or games.
     *
     * You may get up to 100 categories or games by specifying their ID or name. You may specify all IDs, all names, or a combination of IDs and names. If you specify a combination of IDs and names, the total number of IDs and names must not exceed 100.
     *
     *  Requires an **app access token** or **user access token.**
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-games
     */
    case GetGames = 'games';

    /**
     * Gets all users that the broadcaster banned or put in a timeout.
     *
     * Requires a **user access token** that includes the **moderation:read** or **moderator:manage:banned_users** scope.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-banned-users
     */
    case GetBannedUsers = 'moderation/banned';

    /**
     * Gets a list of channels that the specified user has moderator privileges in.
     *
     * - Requires a **user access token** that includes the **user:read:moderated_channels** scope.
     * - Query parameter user_id must match the user ID in the **User-Access token**
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-moderated-channels
     */
    case GetModeratedChannels = 'moderation/channels';

    /**
     * Gets all users allowed to moderate the broadcaster’s chat room.
     *
     * Requires a **user access token** that includes the **moderation:read** scope.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-moderators
     */
    case GetModerators = 'moderation/moderators';

    /**
     * Gets a list of the broadcaster’s VIPs.
     *
     * Requires a **user access token** that includes the **channel:read:vips** scope.
     *
     * @link https://dev.twitch.tv/docs/api/reference#get-vips
     */
    case GetVIPs = 'channels/vips';

    /**
     * Gets the games or categories that match the specified query.
     *
     * To match, the category’s name must contain all parts of the query string. For example, if the query string is 42, the response includes any category name that contains 42 in the title. If the query string is a phrase like love computer, the response includes any category name that contains the words love and computer anywhere in the name. The comparison is case insensitive.
     *
     * Requires an **app access token** or **user access token**.
     *
     * @link https://dev.twitch.tv/docs/api/reference#search-categories
     */
    case SearchCategories = 'search/categories';

    /**
     * Returns the DTO for this Endpoint
     *
     * @return class-string<TwitchDtoInterface>|null
     */
    public function getDataTransferObject(): ?string
    {
        return match ($this) {
            self::GetClips => ClipDto::class,
            self::SearchCategories => CategoryDto::class,
            self::GetGames => GameDto::class,
            self::GetClipsDownload => ClipDownloadDto::class,
            default => null
        };
    }
}
