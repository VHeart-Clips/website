<?php

declare(strict_types=1);

namespace App\Services\Twitch\Data;

use App\Services\Twitch\Contracts\TwitchDtoInterface;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Data Transfer Object for Twitch Clips
 * That way we can make it "type safe" and easier to use.
 */
readonly class ClipDto implements TwitchDtoInterface
{
    public function __construct(
        public string $id,
        public string $url,
        public string $embed_url,
        public int $broadcaster_id,
        public string $broadcaster_name,
        public int $creator_id,
        public string $creator_name,
        public ?int $video_id,
        public int $game_id,
        public string $language,
        public string $title,
        public int $view_count,
        public CarbonInterface $created_at,
        public string $thumbnail_url,
        public float $duration,
        public ?int $vod_offset,
        public bool $is_featured
    ) {}

    public static function from(array $data): static
    {
        // Twitch returns an empty string "" for video_id if unavailable
        $video_id = ! empty($data['video_id']) ? (int) $data['video_id'] : null;
        $created_at = Carbon::parse($data['created_at']);

        return new static(
            (string) $data['id'],
            $data['url'],
            $data['embed_url'],
            (int) $data['broadcaster_id'],
            $data['broadcaster_name'],
            (int) $data['creator_id'],
            $data['creator_name'],
            $video_id,
            (int) $data['game_id'],
            $data['language'],
            $data['title'],
            $data['view_count'],
            $created_at,
            $data['thumbnail_url'],
            $data['duration'],
            $data['vod_offset'],
            $data['is_featured']
        );
    }

    /**
     * Convert this DTO to a Model compatible structure
     */
    public function toModel(?array $data = null): array
    {
        return array_merge([
            'twitch_id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'broadcaster_id' => $this->broadcaster_id,
            'creator_id' => $this->creator_id,
            'game_id' => $this->game_id,
            'vod_id' => $this->video_id,
            'vod_offset' => $this->vod_offset,
            'duration' => $this->duration,
            'language' => $this->language,
            'date' => $this->created_at,
        ], $data ?? []);
    }

    /**
     * @return  array<int, ClipDto>
     */
    public static function fromArray(array $dataList): array
    {
        $result = [];
        foreach ($dataList['data'] as $clip) {
            $result[] = self::from($clip);
        }

        return $result;
    }
}
