<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\Ban;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\LazyCollection;

class BansSection extends BaseSection
{
    public function title(): string
    {
        return 'Bans';
    }

    public function shouldExport(User $user): bool
    {
        return $this->query($user)->exists();
    }

    public function export(User $user): LazyCollection
    {
        return $this->query($user)
            ->lazyByIdDesc()
            ->map(fn (Ban $ban): array => [
                'id' => $ban->id,
                'type' => $ban->bannable_type,
                'reason' => $ban->reason,
                'banned_until' => $ban->banned_until?->toIso8601ZuluString(),
                'unbanned_at' => $ban->unbanned_at?->toIso8601ZuluString(),
                'created_at' => $ban->created_at->toIso8601ZuluString(),
                'updated_at' => $ban->updated_at->toIso8601ZuluString(),
            ]);
    }

    /**
     * @return Builder<Ban>
     */
    private function query(User $user): Builder
    {
        $userMorphs = [
            new User()->getMorphClass(),
            new Broadcaster()->getMorphClass(),
        ];

        return Ban::query()
            ->whereIn('bannable_type', $userMorphs)
            ->where('bannable_id', $user->getKey());
    }
}
