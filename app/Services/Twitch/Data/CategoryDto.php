<?php

declare(strict_types=1);

namespace App\Services\Twitch\Data;

use App\Services\Twitch\Contracts\TwitchDtoInterface;

/* @link https://dev.twitch.tv/docs/api/reference#search-categories */
readonly class CategoryDto implements TwitchDtoInterface
{
    public function __construct(
        public int $id,
        public string $name,
        public string $boxArtUrl, // box_art_url
    ) {}

    public static function from(array $data): static
    {
        return new static(
            id: (int) $data['id'],
            name: $data['name'],
            boxArtUrl: str_replace('52x72', '{width}x{height}', $data['box_art_url']),
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
            'title' => $this->name,
            'box_art' => $this->boxArtUrl,
        ], $extra);
    }
}
