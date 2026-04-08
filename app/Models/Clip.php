<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\TwitchClipThumbnailCast;
use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Clips\ClipStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\ClipVoteType;
use App\Enums\ExternalContentProxyType;
use App\Enums\FeatureFlag;
use App\Http\Resources\PublicClipResource;
use App\Models\Contracts\ExternalProxyable;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\Traits\Auditable;
use App\Models\Traits\Clip\ClipRelationships;
use App\Models\Traits\Clip\ClipToClipCompilationRelationships;
use App\Models\Traits\HasExternalProxy;
use App\Models\Traits\Reportable;
use App\Policies\ClipPolicy;
use App\Support\FeatureFlag\Feature;
use Carbon\CarbonInterval;
use Database\Factories\ClipFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

#[ScopedBy(ClipPermissionScope::class)]
#[ScopedBy(ClipWithoutBannedCategoryScope::class)]
#[UseResource(PublicClipResource::class)]
#[UsePolicy(ClipPolicy::class)]
class Clip extends Model implements Commentable, ExternalProxyable
{
    use Auditable;
    use ClipRelationships;
    use ClipToClipCompilationRelationships;
    use HasComments;
    use HasExternalProxy;

    /** @use HasFactory<ClipFactory> */
    use HasFactory;

    use Reportable;
    use SoftDeletes;

    public static function getProxyIdentifierColumn(): string
    {
        return 'twitch_id';
    }

    public static function getProxyUrlColumn(): string
    {
        return 'thumbnail_url';
    }

    public static function getProxyExtension(): string
    {
        return 'jpg';
    }

    /**
     * Returns the Twitch Clip Url for Twitch
     */
    public function getClipUrl(): string
    {
        // old ui, but less buggy
        return "https://clips.twitch.tv/{$this->twitch_id}";
    }

    public function getReportableTitleAttribute(): string
    {
        return 'title';
    }

    public function getProxyType(): ExternalContentProxyType
    {
        return ExternalContentProxyType::TwitchClip;
    }

    protected function casts(): array
    {
        return [
            'thumbnail_url' => TwitchClipThumbnailCast::class,
            'date' => 'immutable_datetime',
            'status' => ClipStatus::class,
        ];
    }

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

    #[Scope]
    protected function whereArchived(Builder $query): Builder
    {
        return $query->whereNotNull(['final_jury_votes', 'final_public_votes', 'final_score']);
    }

    #[Scope]
    protected function whereNotArchived(Builder $query): Builder
    {
        return $query->whereNull(['final_jury_votes', 'final_public_votes', 'final_score']);
    }

    /**
     * Get Clips that should be archived
     *
     * We will allow a 1 week buffer before we permanently archive clips though, just in case something changes
     */
    #[Scope]
    protected function whereEligibleForArchival(Builder $query): Builder
    {
        /** @var CarbonInterval $maxAge */
        $maxAge = config('vheart.clips.voting.maximum_age');

        return $query
            ->whereNotArchived()
            ->where(function (Builder $query) use ($maxAge): void {
                $query
                    ->where('created_at', '<', now()->sub($maxAge)->subWeek())
                    ->orWhereHas('compilations',
                        fn (Builder $q) => $q
                            ->whereIn('compilations.status', CompilationStatus::getVoteDisabledCases())
                            ->where('compilations.updated_at', '<', now()->subWeek())
                    );
            });
    }

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
            return $query->whereRaw('1 = 0');
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
            $q->whereIn('compilations.status', array_merge(
                CompilationStatus::getPublicCases(),
                [CompilationStatus::Scheduled]
            ));
        });
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

    private function extractUserIdFromParameter(User|int $userOrId): int
    {
        return $userOrId instanceof User ? $userOrId->id : $userOrId;
    }
}
