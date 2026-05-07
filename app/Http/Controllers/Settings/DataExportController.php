<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Enums\ClipVoteType;
use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Broadcaster\BroadcasterConsentLog;
use App\Models\Broadcaster\BroadcasterSubmissionFilter;
use App\Models\Broadcaster\BroadcasterTeamMember;
use App\Models\Clip;
use App\Models\Report;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\User;
use App\Models\Vote;
use App\Support\Audit\Auditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use JsonException;

class DataExportController extends Controller
{
    /**
     * @throws JsonException
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $user->loadMissing([
            'submittedClips' => fn (HasMany $q) => $q->withoutGlobalScopes([ClipPermissionScope::class, ClipWithoutBannedCategoryScope::class])->withTrashed(),
            'broadcastedClips' => fn (HasMany $q) => $q->withoutGlobalScopes([ClipPermissionScope::class, ClipWithoutBannedCategoryScope::class])->withTrashed(),
            'createdClips' => fn (HasMany $q) => $q->withoutGlobalScopes([ClipPermissionScope::class, ClipWithoutBannedCategoryScope::class])->withTrashed(),
            'votes' => fn (HasMany $q) => $q->with(['clip' => fn (BelongsTo $clip) => $clip->withTrashed()->get(['id', 'twitch_id'])]),
            'broadcasterTeamMembers',
        ]);

        Auditor::make()
            ->causer($user)
            ->on($user)
            ->event('data-export')
            ->save();

        $data = [
            'exported_at' => now()->toIso8601ZuluString(),

            'account' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at?->toIso8601ZuluString(),
            ],

            'broadcaster' => $this->broadcaster($user),

            'clips' => $this->clips($user),
            'submitted_clips' => $user->submittedClips->pluck('twitch_id'),
            'broadcasted_clips' => $user->broadcastedClips->pluck('twitch_id'),
            'created_clips' => $user->createdClips->pluck('twitch_id'),

            'votes' => $this->votes($user),
            'reports' => $this->filedReports($user),
            'broadcaster_team_memberships' => $this->userTeams($user),
            'compilations' => $this->compilations($user),
            'audits' => $this->audits($user),
            'consent_logs' => $this->consentLogs($user),
        ];

        return Response(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            headers: [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="'.$user->id.'-export-data-'.now()->format('Y-m-d_H-i-s').'.json"',
            ]
        );
    }

    private function votes(User $user): Collection
    {
        return $user->votes
            ?->map(fn (Vote $vote): array => [
                'clip_id' => $vote->clip?->twitch_id,
                'type' => $vote->type,
                'type_text' => ClipVoteType::tryFrom($vote->type)?->name ?? 'Unknown',
                'state' => $vote->voted,
                'state_text' => $vote->voted ? 'Voted' : 'Skipped',
                'voted_at' => $vote->created_at?->toIso8601ZuluString(),
            ]);
    }

    private function userTeams(User $user): Collection
    {
        return $user->broadcasterTeamMembers
            ?->map(fn ($member): array => [
                'broadcaster_id' => $member->broadcaster_id,
                'permissions' => $member->permissions?->map->name,
                'joined_at' => $member->created_at?->toIso8601ZuluString(),
            ]);
    }

    private function audits(User $user): Collection
    {
        return Audit::query()
            ->where(fn (Builder $query) => $query
                ->where('auditable_id', $user->id)
                ->where('auditable_type', (new User)->getMorphClass())
            )
            ->orWhere(fn (Builder $query) => $query
                ->where('causer_id', $user->id)
                ->where('causer_type', (new User)->getMorphClass())
            )
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Audit $audit): array => [
                'id' => $audit->id,
                'causer_id' => $audit->causer_id,
                'auditable_type' => $audit->auditable_type,
                'auditable_id' => $audit->auditable_id,
                'event' => $audit->event,
                'request_id' => $audit->request_id,
                'ip_address' => $audit->ip_address,
                'user_agent' => $audit->user_agent,
                'created_at' => $audit->created_at->toIso8601ZuluString(),
            ]);
    }

    private function compilations(User $user): Collection
    {
        return Clip\Compilation::query()
            ->withTrashed()
            ->where('user_id', $user->id)
            ->get()
            ->map(fn (Clip\Compilation $compilation): array => [
                'title' => $compilation->title,
                'description' => $compilation->description,
                'status' => $compilation->status->value,
                'status_text' => $compilation->status->getLabel(),
                'type' => $compilation->type->value,
                'type_text' => $compilation->type->getLabel(),
                'youtube_url' => $compilation->youtube_url,
                'created_at' => $compilation->created_at?->toIso8601ZuluString(),
                'deleted_at' => $compilation->deleted_at?->toIso8601ZuluString(),
            ]);
    }

    private function filedReports(User $user): Collection
    {
        return Report::query()
            ->withTrashed()
            ->where('user_id', $user->id)
            ->get()
            ->map(fn (Report $report): array => [
                'id' => $report->id,
                'reason' => $report->reason->value,
                'reason_text' => $report->reason->getLabel(),
                'description' => $report->description,
                'status' => $report->status->value,
                'status_text' => $report->status->getLabel(),
                'reported_at' => $report->created_at?->toIso8601ZuluString(),
            ]);
    }

    private function clips(User $user): Collection
    {
        $ids = $user->submittedClips->pluck('id')
            ->merge($user->broadcastedClips->pluck('id'))
            ->merge($user->createdClips->pluck('id'))
            ->unique();

        return Clip::withTrashed()
            ->withoutGlobalScopes([ClipPermissionScope::class, ClipWithoutBannedCategoryScope::class])
            ->whereIn('id', $ids)
            ->with(['tags:id,name', 'category:id,title'])
            ->get()
            ->map(fn (Clip $clip): array => [
                'id' => $clip->twitch_id,
                'title' => $clip->title,
                'status' => $clip->status->value,
                'status_text' => $clip->status->getLabel(),
                'duration' => $clip->duration,
                'language' => $clip->language,
                'date' => $clip->date->toIso8601ZuluString(),
                'tags' => $clip->tags->pluck('name'),
                'category' => $clip->category?->id,
                'category_text' => $clip->category?->title,
                'submitted_at' => $clip->created_at?->toIso8601ZuluString(),
                'deleted_at' => $clip->deleted_at?->toIso8601ZuluString(),
            ]);
    }

    private function consentLogs(User $user): Collection
    {
        return BroadcasterConsentLog::query()
            ->where('broadcaster_id', $user->id)
            ->orderBy('changed_at')
            ->get()
            ->map(fn (BroadcasterConsentLog $log): array => [
                'state' => $log->state->map->name,
                'changed_by' => $log->changed_by,
                'change_reason' => $log->change_reason,
                'changed_at' => $log->changed_at->toIso8601ZuluString(),
            ]);
    }

    private function broadcaster(User $user): ?array
    {
        $broadcaster = $user->broadcaster;

        if (! $broadcaster) {
            return null;
        }

        $broadcaster->loadMissing([
            'members',
            'filters',
        ]);

        return [
            'consent' => $broadcaster->consent?->map->name,

            'submission_rules' => [
                'everyone' => $broadcaster->submit_user_allowed,
                'vip' => $broadcaster->submit_vip_allowed,
                'mods' => $broadcaster->submit_mods_allowed,
            ],

            'onboarded_at_info' => 'Onboarded At will only be set after manual onboarding has been done',
            'onboarded_at' => $broadcaster->onboarded_at?->toIso8601ZuluString(),

            'team_members' => $broadcaster->members
                ?->map(fn (BroadcasterTeamMember $member): array => [
                    'identifier' => $member->user_id,
                    'permissions' => $member->permissions,
                    'added_at' => $member->created_at?->toIso8601ZuluString(),
                ]),

            'filters' => $broadcaster->filters
                ?->map(fn (BroadcasterSubmissionFilter $filter): array => [
                    'type' => $filter->filterable_type,
                    'identifier' => $filter->filterable_id,
                    'state' => $filter->state,
                    'state_text' => $filter->state ? 'Allowed' : 'Not Allowed',
                    'created_at' => $filter->created_at?->toIso8601ZuluString(),
                ]),
        ];
    }
}
