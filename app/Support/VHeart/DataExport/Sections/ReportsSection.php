<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\Broadcaster\Broadcaster;
use App\Models\Clip;
use App\Models\Report;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\LazyCollection;

class ReportsSection extends BaseSection
{
    public function title(): string
    {
        return 'Reports';
    }

    public function shouldExport(User $user): bool
    {
        return $this->query($user)->exists();
    }

    public function export(User $user): LazyCollection
    {
        return $this->query($user)
            ->with([
                'category' => fn (BelongsTo $q): BelongsTo => $q->withTrashed(),
                'reportable' => fn (BelongsTo $q): BelongsTo => $q->withTrashed(),
            ])
            ->lazyByIdDesc()
            ->map(function (Report $report) use ($user): array {
                $createdByThisUser = $report->user_id === $user->getKey();

                return [
                    'id' => $report->id,
                    'created_by_you' => $createdByThisUser,
                    'category_id' => $report->category->id,
                    'category' => $report->category->name,
                    'reportable_type' => $report->reportable_type,
                    'reportable_id' => match (true) {
                        $report->reportable instanceof Clip => $report->reportable->twitch_id,
                        $report->reportable_type === (new Clip)->getMorphClass() => null,
                        default => $report->reportable_id,
                    },
                    'description' => $createdByThisUser ? $report->description : null,
                    'status' => $report->status,
                    'resolve_action' => $report->resolve_action,
                    'reported_at' => $createdByThisUser ? $report->created_at?->toIso8601ZuluString() : null,
                    'resolved_at' => $report->resolved_at?->toIso8601ZuluString(),
                    'deleted_at' => $createdByThisUser ? $report->deleted_at?->toIso8601ZuluString() : null,
                ];
            });
    }

    private function query(User $user): Builder
    {
        return Report::query()
            ->withTrashed()
            ->where(fn (Builder $query): Builder => $query
                // by this user
                ->where('user_id', $user->getKey())
                // on this user
                ->orWhere(fn (Builder $q): Builder => $q
                    ->where('reportable_id', $user->getKey())
                    ->where('reportable_type', $user->getMorphClass())
                )
                // on the broadcaster of this user
                ->orWhere(fn (Builder $q): Builder => $q
                    ->where('reportable_id', $user->getKey())
                    ->where('reportable_type', new Broadcaster()->getMorphClass())
                )
                // on clips by the broadcaster of this user
                ->orWhereHasMorph(
                    'reportable',
                    [Clip::class],
                    fn (Builder $q): Builder => $q
                        ->withTrashed()
                        ->withoutGlobalScopes([
                            ClipPermissionScope::class,
                            ClipWithoutBannedCategoryScope::class,
                        ])
                        ->where('broadcaster_id', $user->getKey()),
                )
            );
    }
}
