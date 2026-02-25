<?php

declare(strict_types=1);

namespace App\Services\Twitch\Data;

use App\Services\Twitch\Contracts\TwitchDtoInterface;
use App\Services\Twitch\Enums\TwitchBroadcasterType;
use App\Services\Twitch\Enums\TwitchUserType;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/* https://dev.twitch.tv/docs/api/reference#get-users */
readonly class UserDto implements TwitchDtoInterface
{
    public function __construct(
        public string $id,
        public string $login,
        public string $display_name,
        public TwitchUserType $type,
        public TwitchBroadcasterType $broadcaster_type,
        public string $description,
        public string $profile_image_url,
        public string $offline_image_url,
        public ?string $email,
        public CarbonInterface $created_at,
    ) {}

    public static function from(array $data): static
    {
        $created_at = Carbon::parse($data['created_at']);
        $userType = TwitchUserType::tryFrom($data['type']) ?? TwitchUserType::User;
        $broadcasterType = TwitchBroadcasterType::tryFrom($data['broadcaster_type']) ?? TwitchBroadcasterType::Normal;
        $email = empty($data['email']) ? null : $data['email'];

        return new self(
            id: $data['id'],
            login: $data['login'],
            display_name: $data['display_name'],
            type: $userType,
            broadcaster_type: $broadcasterType,
            description: $data['description'],
            profile_image_url: $data['profile_image_url'],
            offline_image_url: $data['offline_image_url'],
            email: $email,
            created_at: $created_at,
        );
    }

    /**
     * @return array<int, UserDto>
     */
    public static function fromArray(array $dataList): array
    {
        $result = [];
        foreach ($dataList['data'] as $clip) {
            $result[] = self::from($clip);
        }

        return $result;
    }

    public function toModel(?array $data = null): array
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->display_name,
            'avatar_url' => $this->profile_image_url,
        ], $data ?? []);
    }
}
