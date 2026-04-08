<?php

declare(strict_types=1);

namespace App\Models\Traits\Clip\Scopes;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Clips\CompilationStatus;
use App\Enums\FeatureFlag;
use App\Models\Clip;
use App\Models\User;
use App\Support\FeatureFlag\Feature;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Clip
 */
trait ClipFilterScopes
{
    /**
     * Exclude Clips that has been Submitted before a date
     */
    #[Scope]
    protected function whereSubmittedAfter(Builder $query, DateTimeInterface $dateTime): Builder
    {
        return $query->where('created_at', '>=', $dateTime);
    }

    /**
     * Exclude Clips that has been Clipped before a date
     */
    #[Scope]
    protected function whereClippedAfter(Builder $query, DateTimeInterface $dateTime): Builder
    {
        return $query->where('date', '>=', $dateTime);
    }

    /**
     * Include only Clips where the broadcaster has explicitly granted content use permission.
     */
    #[Scope]
    protected function whereBroadcasterGavePermission(Builder $query, BroadcasterConsent|Collection|array|null $consents = null): Builder
    {
        if (Feature::isActive(FeatureFlag::IgnoreBroadcasterConsent)) {
            return $query;
        }

        return $query->whereHas('broadcaster',
            fn (Builder $q) => $q->whereGaveConsent($consents)
        );
    }

    /**
     * Exclude Clips where the broadcaster has not granted content use permission.
     */
    #[Scope]
    protected function whereBroadcasterDeniedPermission(Builder $query): Builder
    {
        if (Feature::isActive(FeatureFlag::IgnoreBroadcasterConsent)) {
            return $query->whereRaw(DB::raw('1 = 0'));
        }

        return $query->whereDoesntHave('broadcaster',
            fn (Builder $q) => $q->whereGaveNoConsent()
        );
    }

    /**
     * Exclude Clips that are attached to a published or scheduled Compilation
     */
    #[Scope]
    protected function whereNotPublished(Builder $query): Builder
    {
        return $query->whereDoesntHave('compilations', function (Builder $q): void {
            $q->whereIn('compilations.status', CompilationStatus::getVoteDisabledCases());
        });
    }

    /**
     * Exclude Clips the user has Broadcasted
     */
    #[Scope]
    protected function whereNotBroadcastBy(Builder $query, User|int $userOrId): Builder
    {
        $userId = $this->extractUserIdFromParameter($userOrId);

        return $query->whereNot('broadcaster_id', $userId);
    }

    /**
     * Exclude Clips the user has Created/Clipped
     */
    #[Scope]
    protected function whereNotCreatedBy(Builder $query, User|int $userOrId): Builder
    {
        $userId = $this->extractUserIdFromParameter($userOrId);

        return $query->whereNot('creator_id', $userId);
    }

    /**
     * Exclude Clips the user has Submitted
     */
    #[Scope]
    protected function whereNotSubmittedBy(Builder $query, User|int $userOrId): Builder
    {
        $userId = $this->extractUserIdFromParameter($userOrId);

        return $query->whereNot('submitter_id', $userId);
    }

    /**
     * Include only Clips the user has Broadcasted
     */
    #[Scope]
    protected function whereBroadcastBy(Builder $query, User|int $userOrId): Builder
    {
        $userId = $this->extractUserIdFromParameter($userOrId);

        return $query->where('broadcaster_id', $userId);
    }
}
