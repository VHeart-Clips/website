<?php

declare(strict_types=1);

namespace App\Http\Resources\Clip;

use App\Models\Clip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Clip
 */
class ClipVoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->twitch_id,
            'title' => $this->title,
            'duration' => $this->duration,
            'url' => $this->getClipUrl(),
            'thumbnail_url' => $this->proxiedContentUrl(),

            'broadcaster' => [
                'id' => $this->broadcaster_id,
                'name' => $this->owner->name,
                'avatar' => $this->owner->proxiedContentUrl(),
            ],
        ];
    }
}
