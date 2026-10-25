<?php

declare(strict_types=1);

namespace App\Services\Twitch\Data;

use App\Services\Twitch\Contracts\TwitchDtoInterface;
use App\Services\Twitch\Enums\TwitchBroadcasterType;
use App\Services\Twitch\Enums\TwitchUserType;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

/* @link https://dev.twitch.tv/docs/api/reference#get-users */
readonly class UserDto implements TwitchDtoInterface
{
    public function __construct(
        public string $id,
        public string $login,
        public string $displayName, // display_name
        public TwitchUserType $type,
        public TwitchBroadcasterType $broadcasterType, // broadcaster_type
        public string $description,
        public string $profileImageUrl, // profile_image_url
        public string $offlineImageUrl, // offline_image_url
        public ?string $email,
        public CarbonInterface $createdAt, // created_at
    ) {}

    public static function from(array $data): static
    {
        return new static(
            id: $data['id'],
            login: $data['login'],
            displayName: $data['display_name'],
            type: TwitchUserType::tryFrom($data['type']) ?? TwitchUserType::User,
            broadcasterType: TwitchBroadcasterType::tryFrom($data['broadcaster_type']) ?? TwitchBroadcasterType::Normal,
            description: $data['description'],
            profileImageUrl: $data['profile_image_url'],
            offlineImageUrl: $data['offline_image_url'],
            email: empty($data['email']) ? null : $data['email'],
            createdAt: Date::parse($data['created_at']),
        );
    }

    /** @return list<static> */
    public static function fromCollection(array $response): array
    {
        return array_map(static::from(...), $response['data']);
    }

    public function toModel(array $extra = []): array
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->displayName,
            'avatar_url' => $this->profileImageUrl,
        ], $extra);
    }
}
