<?php

declare(strict_types=1);

namespace App\Models\Traits\Clip\Scopes;

use App\Enums\ClipVoteType;
use App\Enums\FeatureFlag;
use App\Models\Clip;
use App\Models\User;
use App\Models\Vote;
use App\Support\FeatureFlag\Feature;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Clip
 */
trait ClipVoteScopes
{
    /**
     * add rules/filters here to limit what can be voted on.
     */
    #[Scope]
    protected function whereEligibleForVoting(Builder $query, ?User $user = null): Builder
    {
        /** @var CarbonInterval $maxAge */
        $maxAge = config('vheart.clips.voting.maximum_age');

        if (! Feature::isActive(FeatureFlag::ClipVoting)) {
            // Since the feature got disabled, make it impossible to get anything to vote on
            return $query->whereRaw('1 = 0');
        }

        // Make sure to sort the rules in a way that allows the biggest scope to filter the most first
        return $query
            ->whereNotArchived()
            ->whereSubmittedAfter(now()->sub($maxAge))
            ->whereBroadcasterGavePermission()
            ->whereNotPublished()
            ->when($user, fn (Builder $query) => $query
                ->whereNotBroadcastBy($user)
                ->whereNotCreatedBy($user)
                ->whereNotSubmittedBy($user)
                ->whereNoVotesFrom($user)
            );
    }

    /**
     * Exclude Clips that user has voted on
     */
    #[Scope]
    protected function whereNoVotesFrom(Builder $query, User|int $userOrId): Builder
    {
        $userId = $this->extractUserIdFromParameter($userOrId);

        return $query->whereDoesntHave('votes', fn (Builder $q) => $q->where('user_id', $userId));
    }

    /**
     * Include only Clips that user has voted on
     */
    #[Scope]
    protected function whereVotesFrom(Builder $query, User|int $userOrId): Builder
    {
        $userId = $this->extractUserIdFromParameter($userOrId);

        return $query->whereHas('votes', fn (Builder $q) => $q->where('user_id', $userId));
    }

    /**
     * Counts absolute votes as `absolute_votes`
     */
    #[Scope]
    protected function withAbsoluteVoteCount(Builder $query): Builder
    {
        return $query->withCount([
            'votes as absolute_votes' => fn ($q) => $q->where('voted', true),
        ]);
    }

    /**
     * Calculates the Clip Score as `score`
     */
    #[Scope]
    protected function withScore(Builder $query): Builder
    {
        $juryWeight = (int) config('vheart.clips.scoring.jury_weight', 10);
        $publicWeight = (int) config('vheart.clips.scoring.public_weight', 1);

        if (empty($query->getQuery()->columns)) {
            $query->addSelect($query->getModel()->getTable().'.*');
        }

        return $query->selectSub(
            Vote::query()
                ->selectRaw('COALESCE(SUM(CASE WHEN type = ?::integer THEN ?::integer ELSE ?::integer END), 0)', [ClipVoteType::Jury->value, $juryWeight, $publicWeight])
                ->whereColumn('clip_id', 'clips.id')
                ->where('voted', true),
            'score'
        );
    }

    /**
     * Counts public votes as `public_votes`.
     */
    #[Scope]
    protected function withPublicVoteCount(Builder $query): Builder
    {
        return $query->withCount(
            [
                'votes as public_votes' => function (Builder $query): void {
                    $query
                        ->where('voted', true)
                        ->where('type', ClipVoteType::Public);
                },
            ]
        );
    }

    /**
     * Counts jury votes as `jury_votes`.
     */
    #[Scope]
    protected function withJuryVoteCount(Builder $query): Builder
    {
        return $query->withCount(
            [
                'votes as jury_votes' => function (Builder $query): void {
                    $query
                        ->where('voted', true)
                        ->where('type', ClipVoteType::Jury);
                },
            ]
        );
    }

    /**
     * Counts Votes
     * - Jury votes as `jury_votes`
     * - Public votes as `public_votes`
     */
    #[Scope]
    protected function withVoteCount(Builder $query): Builder
    {
        return $query
            ->withJuryVoteCount()
            ->withPublicVoteCount();
    }
}
