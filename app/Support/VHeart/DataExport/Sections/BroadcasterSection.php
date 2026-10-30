<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\Broadcaster\Broadcaster;
use App\Models\Broadcaster\BroadcasterSubmissionFilter;
use App\Models\Broadcaster\BroadcasterTeamMember;
use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;

class BroadcasterSection extends BaseSection
{
    public function title(): string
    {
        return 'Broadcaster';
    }

    public function shouldExport(User $user): bool
    {
        return $user->broadcaster()->withTrashed()->exists();
    }

    public function export(User $user): array
    {
        $broadcaster = Broadcaster::withTrashed()
            ->with(['filters', 'members'])
            ->findOrFail($user->id);

        return [
            'consent' => $broadcaster->consent,
            'default_clip_status' => $broadcaster->default_clip_status,
            'twitch_mod_permissions' => $broadcaster->twitch_mod_permissions,

            'submission_rules' => [
                'everyone' => $broadcaster->submit_user_allowed,
                'vip' => $broadcaster->submit_vip_allowed,
                'mods' => $broadcaster->submit_mods_allowed,
            ],

            'onboarded_at' => $broadcaster->onboarded_at?->toIso8601ZuluString(),

            'members' => $broadcaster->members
                ->map(fn (BroadcasterTeamMember $member): array => [
                    'id' => $member->user_id,
                    'permissions' => $member->permissions,
                    'created_at' => $member->created_at?->toIso8601ZuluString(),
                    'updated_at' => $member->updated_at?->toIso8601ZuluString(),
                ]),

            'filters' => $broadcaster->filters
                ->map(fn (BroadcasterSubmissionFilter $filter): array => [
                    'type' => $filter->filterable_type,
                    'identifier' => $filter->filterable_id,
                    'state' => $filter->state,
                    'created_at' => $filter->created_at?->toIso8601ZuluString(),
                    'updated_at' => $filter->updated_at?->toIso8601ZuluString(),
                ]),
        ];
    }
}
