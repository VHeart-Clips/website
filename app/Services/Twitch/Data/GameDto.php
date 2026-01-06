<?php

declare(strict_types=1);

namespace App\Services\Twitch\Data;

class GameDto implements TwitchDtoInterface
{
    public function __construct(
        public int $id,
        public string $name,
        public string $box_art_url,
        public ?int $igdb_id = null,
    ) {}

    public static function from(array $data): static
    {
        $igdb_id = empty($data['igdb_id']) ? null : (int) $data['igdb_id'];
        return new static(
            (int) $data['id'],
            $data['name'],
            $data['box_art_url'],
            $igdb_id,
        );
    }

    /**
     * @return array<int, GameDto>
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
            'title' => $this->name,
            'box_art' => $this->box_art_url,
        ], $data ?? []);
    }
}
