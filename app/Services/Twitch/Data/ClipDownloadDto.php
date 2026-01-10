<?php

declare(strict_types=1);

namespace App\Services\Twitch\Data;

class ClipDownloadDto implements TwitchDtoInterface
{
    public function __construct(
        public string $clip_id,
        public ?string $landscape_download_url,
        public ?string $portrait_download_url = null,
    ) {}

    public static function from(array $data): self
    {
        return new static(
            $data['id'],
            $data['landscape_download_url'],
            $data['portrait_download_url'],
        );
    }

    public static function fromArray(array $dataList): array
    {
        $result = [];
        foreach ($dataList['data'] as $clip) {
            $result[] = self::from($clip);
        }

        return $result;
    }

    public function toModel(?array $data): array
    {
        return [];
    }
}
