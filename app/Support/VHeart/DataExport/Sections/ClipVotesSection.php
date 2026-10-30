<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\User;
use App\Models\Vote;
use App\Support\VHeart\DataExport\BaseSection;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\LazyCollection;

class ClipVotesSection extends BaseSection
{
    public function title(): string
    {
        return 'Clip Votes';
    }

    public function shouldExport(User $user): bool
    {
        return $user->votes()->exists();
    }

    public function export(User $user): LazyCollection
    {
        return $user->votes()
            ->with(['clip' => fn (Builder $q): Builder => $q
                ->withTrashed()
                ->withoutGlobalScopes([
                    ClipPermissionScope::class,
                    ClipWithoutBannedCategoryScope::class,
                ])
                ->select(['id', 'twitch_id'])]
            )
            ->lazyByIdDesc()
            ->map(fn (Vote $vote): array => [
                'clip_id' => $vote->clip?->twitch_id,
                'type' => $vote->type,
                'voted' => $vote->voted,
                'created_at' => $vote->created_at?->toIso8601ZuluString(),
                'updated_at' => $vote->updated_at?->toIso8601ZuluString(),
            ]);
    }
}
