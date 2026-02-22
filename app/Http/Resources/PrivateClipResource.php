<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Clip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Clip
 */
class PrivateClipResource extends JsonResource
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
            'thumbnail_url' => $this->proxiedContentUrl(),
            'clip_url' => $this->getClipUrl(),

            'broadcaster' => $this->whenLoaded('broadcaster', [
                'id' => $this->broadcaster_id,
                'name' => $this->broadcaster->name,
                'avatar' => $this->broadcaster->proxiedContentUrl(),
            ]),

            'clipper' => $this->whenLoaded('creator', [
                'id' => $this->creator_id,
                'name' => $this->creator?->name,
                'avatar' => $this->creator?->proxiedContentUrl(),
            ]),

            'submitter' => $this->whenLoaded('submitter', [
                'id' => $this->submitter_id,
                'name' => $this->submitter?->name,
                'avatar' => $this->submitter?->proxiedContentUrl(),
            ]),

            'category' => $this->whenLoaded('category', $this->category->toResource()),

            'vod' => $this->when($this->vod_id, [
                'id' => $this->vod_id,
                'offset' => $this->vod_offset,
            ]),
            'votes' => $this->whenCounted('votes', default: 0),
            'clip_duration' => $this->duration,
            'clipped_at' => $this->date,
            'submitted_at' => $this->created_at,
            'tags' => $this->whenLoaded('tags', $this->tags->toResourceCollection()),
            'status' => $this->status->getLabel(),
            'in_compilation' => false,
        ];
    }
}
