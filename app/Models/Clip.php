<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Clips\ClipStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\ClipVoteType;
use App\Enums\ExternalContentProxyType;
use App\Http\Resources\PublicClipResource;
use App\Models\Clip\Compilation;
use App\Models\Clip\CompilationClip;
use App\Models\Clip\Tag;
use App\Models\Contracts\ExternalProxyable;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\Traits\HasExternalProxy;
use App\Models\Traits\Reportable;
use App\Policies\ClipPolicy;
use Database\Factories\ClipFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Vite;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

#[ScopedBy(ClipPermissionScope::class)]
#[ScopedBy(ClipWithoutBannedCategoryScope::class)]
#[UseResource(PublicClipResource::class)]
#[UsePolicy(ClipPolicy::class)]
class Clip extends Model implements Commentable, ExternalProxyable
{
    /** @use HasFactory<ClipFactory> */
    use HasComments, HasExternalProxy, HasFactory, Reportable;

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

    public function broadcaster(): BelongsTo
    {
        return $this->BelongsTo(User::class)
            ->withTrashed()
            ->withDefault(['name' => 'N/A', 'avatar_url' => Vite::asset('resources/images/png/cat.png')]);
    }

    /**
     * Returns the Twitch Clip Url for Twitch
     */
    public function getClipUrl(): string
    {
        // old ui, but less buggy
        return "https://clips.twitch.tv/{$this->twitch_id}";
    }

    public function creator(): BelongsTo
    {
        return $this->BelongsTo(User::class)->withTrashed();
    }

    public function submitter(): BelongsTo
    {
        return $this->BelongsTo(User::class)->withTrashed();
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)
            ->withDefault(Category::Defaults);
    }

    /**
     * @return BelongsToMany<Tag, $this, Pivot>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'clip_tags');
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * @return BelongsToMany<Compilation, $this, Pivot>
     */
    public function compilations(): BelongsToMany
    {
        return $this->belongsToMany(Compilation::class)
            ->using(CompilationClip::class)
            ->withPivot(CompilationClip::getPivotColumns())
            ->withTimestamps();
    }

    public function claimer(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'claimed_by');
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
            'status' => ClipStatus::class,
        ];
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
    protected function whereBroadcasterGavePermission(Builder $query): Builder
    {
        return $query->whereHas('broadcaster',
            fn (Builder $q) => $q->where('clip_permission', true)
        );
    }

    /**
     * Exclude Clips where the broadcaster has not granted content use permission.
     */
    #[Scope]
    protected function whereBroadcasterDeniedPermission(Builder $query): Builder
    {
        return $query->whereDoesntHave('broadcaster',
            fn (Builder $q) => $q->where('clip_permission', true)
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
        $userId = $userOrId instanceof User ? $userOrId->id : $userOrId;

        return $query->whereDoesntHave('votes', fn (Builder $q) => $q->where('user_id', $userId));
    }

    /**
     * Include only Clips that user has voted on
     */
    #[Scope]
    protected function whereVotesFrom(Builder $query, User|int $userOrId): Builder
    {
        $userId = $userOrId instanceof User ? $userOrId->id : $userOrId;

        return $query->whereHas('votes', fn (Builder $q) => $q->where('user_id', $userId));
    }

    /**
     * Exclude Clips the user has Broadcasted
     */
    #[Scope]
    protected function whereNotBroadcastBy(Builder $query, User|int $userOrId): Builder
    {
        $userId = $userOrId instanceof User ? $userOrId->id : $userOrId;

        return $query->whereNot('broadcaster_id', $userId);
    }

    /**
     * Include only Clips the user has Broadcasted
     */
    #[Scope]
    protected function whereBroadcastBy(Builder $query, User|int $userOrId): Builder
    {
        $userId = $userOrId instanceof User ? $userOrId->id : $userOrId;

        return $query->where('broadcaster_id', $userId);
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
                    $query->where('type', ClipVoteType::Public);
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
                    $query->where('type', ClipVoteType::Jury);
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
