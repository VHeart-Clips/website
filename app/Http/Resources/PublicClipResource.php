<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Clip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Clip
 */
class PublicClipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->twitch_id,
            'title' => $this->title,
            'thumbnail_url' => $this->thumbnail_url,
            'clip_url' => $this->getClipUrl(),

            'broadcaster' => $this->whenLoaded('owner', [
                'id' => $this->broadcaster_id,
                'name' => $this->owner->name,
                'avatar' => $this->owner->avatar_url,
            ]),

            'clipper' => $this->whenHas('creator', [
                'id' => $this->creator_id,
                'name' => $this->creator?->name,
                'avatar' => $this->creator?->avatar_url,
            ]),

            'submitter' => $this->whenHas('submitter', [
                'id' => $this->submitter_id,
                'name' => $this->submitter?->name,
                'avatar' => $this->submitter?->avatar_url,
            ]),

            'category' => $this->whenLoaded('category', $this->category->toResource()),

            'vod' => $this->when($this->vod_id, [
                'id' => $this->vod_id,
                'offset' => $this->vod_offset,
            ]),

            'votes' => $this->absolute_votes ?? 0,
            'clip_duration' => $this->duration,
            'clipped_at' => $this->date,
            'submitted_at' => $this->created_at,
        ];
    }
}
