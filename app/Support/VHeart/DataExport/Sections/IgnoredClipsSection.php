<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\Clip;
use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;
use Illuminate\Support\LazyCollection;

class IgnoredClipsSection extends BaseSection
{
    public function title(): string
    {
        return 'Ignored Clips';
    }

    public function shouldExport(User $user): bool
    {
        return $user->ignoredClips()->exists();
    }

    public function export(User $user): LazyCollection
    {
        return $user->ignoredClips()
            ->withPivot('created_at')
            ->latest()
            ->where('user_clip_ignore.created_at', '<=', now())
            ->lazy()
            ->map(fn (Clip $clip): array => [
                'id' => $clip->twitch_id,
                'created_at' => $clip->pivot->created_at->toIso8601ZuluString(),
            ]);
    }
}
