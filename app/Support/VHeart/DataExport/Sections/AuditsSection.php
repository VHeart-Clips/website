<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\Audit;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Clip;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\LazyCollection;

class AuditsSection extends BaseSection
{
    public function title(): string
    {
        return 'Audits';
    }

    public function export(User $user): LazyCollection
    {
        $userMorphs = [
            new User()->getMorphClass(),
            new Broadcaster()->getMorphClass(),
        ];
        $clipMorph = new Clip()->getMorphClass();

        return Audit::query()
            ->with(['auditable' => fn (MorphTo $morphTo) => $morphTo
                ->withTrashed()
                ->constrain([
                    Clip::class => fn (Builder $query) => $query
                        ->withoutGlobalScopes([
                            ClipPermissionScope::class,
                            ClipWithoutBannedCategoryScope::class,
                        ])
                        ->select('id', 'twitch_id'),
                ]),
            ])
            ->where(fn (Builder $query) => $query
                ->where(fn (Builder $q) => $q
                    ->where('auditable_id', $user->id)
                    ->whereIn('auditable_type', $userMorphs)
                )
                ->orWhere(fn (Builder $q) => $q
                    ->where('causer_id', $user->id)
                    ->whereIn('causer_type', $userMorphs)
                )
            )
            ->lazyByIdDesc()
            ->map(function (Audit $audit) use ($clipMorph, $userMorphs, $user): array {
                $causedByThisUser = in_array($audit->causer_type, $userMorphs, true) && $audit->causer_id === $user->id;
                $targetsOtherUser = in_array($audit->auditable_type, $userMorphs, true) && $audit->auditable_id !== $user->id;
                $auditableId = ($audit->auditable_type === $clipMorph) ? $audit->auditable?->twitch_id : $audit->auditable_id;

                // the values can contain any arbitrary data including user data, because of this we can't export the values itself.
                $changedAttributes = array_values(array_unique([
                    ...array_keys($audit->old ?? []),
                    ...array_keys($audit->new ?? []),
                ]));

                return [
                    'caused_by_you' => $causedByThisUser,
                    'auditable_type' => $audit->auditable_type,
                    'auditable_id' => $targetsOtherUser ? null : $auditableId,
                    'event' => $audit->event,
                    'changed' => $targetsOtherUser ? null : $changedAttributes,
                    'tags' => $targetsOtherUser ? null : $audit->tags,
                    'request_id' => $causedByThisUser ? $audit->request_id : null,
                    'ip_address' => $causedByThisUser ? $audit->ip_address : null,
                    'user_agent' => $causedByThisUser ? $audit->user_agent : null,
                    'created_at' => $audit->created_at->toIso8601ZuluString(),
                    'updated_at' => $audit->updated_at->toIso8601ZuluString(),
                ];
            });
    }
}
