<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\Broadcaster\BroadcasterTeamMember;
use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;
use Illuminate\Support\LazyCollection;

class BroadcasterTeamMembershipsSection extends BaseSection
{
    public function title(): string
    {
        return 'Broadcaster Team Memberships';
    }

    public function shouldExport(User $user): bool
    {
        return $user->broadcasterTeamMembers()->exists();
    }

    public function export(User $user): LazyCollection
    {
        return $user->broadcasterTeamMembers()
            ->lazyByIdDesc()
            ->map(fn (BroadcasterTeamMember $member): array => [
                'broadcaster_id' => $member->broadcaster_id,
                'permissions' => $member->permissions,
                'created_at' => $member->created_at?->toIso8601ZuluString(),
            ]);
    }
}
