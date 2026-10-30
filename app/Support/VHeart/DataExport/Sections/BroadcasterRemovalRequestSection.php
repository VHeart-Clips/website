<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\Broadcaster\RemovalRequest;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\LazyCollection;

class BroadcasterRemovalRequestSection extends BaseSection
{
    public function title(): string
    {
        return 'Broadcaster Removal Requests';
    }

    public function shouldExport(User $user): bool
    {
        return RemovalRequest::query()
            ->where('broadcaster_id', $user->getKey())
            ->exists();
    }

    public function export(User $user): LazyCollection
    {
        return RemovalRequest::query()
            ->where('broadcaster_id', $user->getKey())
            ->with(['clip' => fn (BelongsTo $q): BelongsTo => $q
                ->withTrashed()
                ->withoutGlobalScopes([
                    ClipPermissionScope::class,
                    ClipWithoutBannedCategoryScope::class,
                ])
                ->select(['id', 'twitch_id']),
            ])
            ->lazyByIdDesc()
            ->map(fn (RemovalRequest $request): array => [
                'clip_id' => $request->clip?->twitch_id,
                'status' => $request->status,
                'details' => $request->details,
                'requested_at' => $request->created_at?->toIso8601ZuluString(),
                'resolved_at' => $request->resolved_at?->toIso8601ZuluString(),
            ]);
    }
}
