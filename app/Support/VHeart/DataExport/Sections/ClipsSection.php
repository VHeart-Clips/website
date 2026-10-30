<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\Clip;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\LazyCollection;

class ClipsSection extends BaseSection
{
    public function title(): string
    {
        return 'Clips';
    }

    public function export(User $user): LazyCollection
    {
        return Clip::query()
            ->withTrashed()
            ->withoutGlobalScopes([
                ClipPermissionScope::class,
                ClipWithoutBannedCategoryScope::class,
            ])
            ->where(function (Builder $query) use ($user): void {
                $query->orWhere('creator_id', $user->getKey());
                $query->orWhere('submitter_id', $user->getKey());
                $query->orWhere('broadcaster_id', $user->getKey());
            })
            ->with(['tags:id,name'])
            ->orderBy('id')
            ->lazy()
            ->map(fn (Clip $clip): array => [
                'id' => $clip->twitch_id,
                'title' => $clip->title,
                'thumbnail_url' => $clip->thumbnail_url,
                'status' => $clip->status,
                'duration' => $clip->duration,
                'language' => $clip->language,
                'vod_id' => $clip->vod_id,
                'vod_offset' => $clip->vod_offset,
                'tags' => $clip->tags->pluck('name'),
                'category_id' => $clip->category?->id,
                'broadcaster_id' => $clip->broadcaster_id,
                'creator_id' => $clip->creator_id,
                'submitter_id' => $clip->submitter_id,
                'clipped_at' => $clip->date->toIso8601ZuluString(),
                'created_at' => $clip->created_at?->toIso8601ZuluString(),
                'deleted_at' => $clip->deleted_at?->toIso8601ZuluString(),
                'next_refresh_after' => $clip->next_refresh_after?->toIso8601ZuluString(),
            ]);
    }
}
