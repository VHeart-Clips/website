<?php

declare(strict_types=1);

namespace App\Services\Twitch\Data;

use App\Services\Twitch\Contracts\TwitchDtoInterface;
use App\Services\Twitch\Enums\TwitchStreamType;
use Carbon\CarbonImmutable;
use RuntimeException;

/* @link https://dev.twitch.tv/docs/api/reference#get-streams */
readonly class StreamDto implements TwitchDtoInterface
{
    public function __construct(
        public string $id,
        public int $userId, // user_id
        public string $userLogin, // user_login
        public string $userName, // user_name
        public int $gameId, // game_id
        public string $gameName, // game_name
        public TwitchStreamType $type,
        public string $title,
        public array $tags,
        public int $viewerCount, // viewer_count
        public CarbonImmutable $startedAt, // started_at
        public string $language,
        public string $thumbnailUrl, // thumbnail_url
    ) {}

    public static function from(array $data): static
    {
        return new static(
            id: $data['id'],
            userId: $data['user_id'],
            userLogin: $data['user_login'],
            userName: $data['user_name'],
            gameId: (int) ($data['game_id'] ?? 0),
            gameName: $data['game_name'],
            type: TwitchStreamType::tryFrom($data['type']) ?? TwitchStreamType::Error,
            title: $data['title'],
            tags: $data['tags'] ?? [],
            viewerCount: $data['viewer_count'] ?? 0,
            startedAt: CarbonImmutable::parse($data['started_at']),
            language: $data['language'],
            thumbnailUrl: $data['thumbnail_url'],
        );
    }

    /** @return list<static> */
    public static function fromCollection(array $response): array
    {
        return array_map(static::from(...), $response['data']);
    }

    public function toModel(array $extra = []): array
    {
        throw new RuntimeException('not implemented');
    }
}
