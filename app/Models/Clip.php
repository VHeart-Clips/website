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
use App\Enums\Filament\LucideIcon;
use App\Filament\Infolists\Components\TwitchEmbedEntry;
use App\Filament\Resources\Clips\Tables\ClipColumns;
use App\Http\Resources\PublicClipResource;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Clip\Compilation;
use App\Models\Clip\CompilationClip;
use App\Models\Clip\Tag;
use App\Models\Contracts\ExternalProxyable;
use App\Models\Contracts\HasFilamentInfolistEntry;
use App\Models\Contracts\HasFilamentTableColumn;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\Traits\Auditable;
use App\Models\Traits\HasExternalProxy;
use App\Models\Traits\Reportable;
use App\Policies\ClipPolicy;
use App\Support\FeatureFlag\Feature;
use Carbon\CarbonInterval;
use Database\Factories\ClipFactory;
use DateTimeInterface;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component as FilamentSchemaComponent;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Column as FilamentTableColumn;
use Filament\Tables\Columns\Layout\Component as FilamentTableComponent;
use Filament\Tables\Columns\Layout\Split;
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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

#[ScopedBy(ClipPermissionScope::class)]
#[ScopedBy(ClipWithoutBannedCategoryScope::class)]
#[UseResource(PublicClipResource::class)]
#[UsePolicy(ClipPolicy::class)]
class Clip extends Model implements Commentable, ExternalProxyable, HasFilamentInfolistEntry, HasFilamentTableColumn
{
    /** @use HasFactory<ClipFactory> */
    use Auditable, HasComments, HasExternalProxy, HasFactory, Reportable, SoftDeletes;

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

    public static function getFilamentTableColumn(string $name): FilamentTableComponent|FilamentTableColumn
    {
        return Split::make([
            ClipColumns::thumbnail()
                ->imageHeight(30)
                ->getStateUsing(fn (Model $record) => $record->$name->proxiedContentUrl()),
            Split::make([
                ClipColumns::title()->make("{$name}.title"),
            ]),
        ]);
    }

    public static function getFilamentInfolistEntry(string $name): FilamentSchemaComponent
    {
        return Grid::make(2)
            ->schema([
                TwitchEmbedEntry::make("$name.twitch_id")
                    ->hiddenLabel()
                    ->columnSpan(1),

                Grid::make(2)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make("$name.title")
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull()
                            ->hiddenLabel()
                            ->wrap(),

                        TextEntry::make("$name.duration")
                            ->label(__('admin/resources/clips.table.columns.duration'))
                            ->tooltip(__('admin/resources/clips.table.columns.duration'))
                            ->formatStateUsing(fn (int $state): string => $state.'s')
                            ->fontFamily(FontFamily::Mono)
                            ->icon(LucideIcon::Clock)
                            ->color('gray')
                            ->badge(),

                        TextEntry::make("$name.status")
                            ->label('admin/resources/clips.table.columns.status')
                            ->tooltip(__('admin/resources/clips.table.columns.status'))
                            ->icon(LucideIcon::Clipboard)
                            ->translateLabel()
                            ->badge(),

                        TextEntry::make("$name.broadcaster.name")
                            ->label(__('admin/resources/clips.table.columns.broadcaster'))
                            ->tooltip(__('admin/resources/clips.table.columns.broadcaster'))
                            ->icon(LucideIcon::Video)
                            ->color('gray'),

                        TextEntry::make("$name.creator.name")
                            ->label(__('admin/resources/clips.table.columns.creator'))
                            ->tooltip(__('admin/resources/clips.table.columns.creator'))
                            ->icon(LucideIcon::Scissors)
                            ->color('gray'),

                        TextEntry::make("$name.tags.name")
                            ->label('Tags')
                            ->color('gray')
                            ->columnSpanFull()
                            ->badge(),
                    ]),
            ]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broadcaster_id', 'id');
    }

    /**
     * @return BelongsTo<Broadcaster, $this>
     */
    public function broadcaster(): BelongsTo
    {
        return $this->belongsTo(Broadcaster::class);
    }

    /**
     * Returns the Twitch Clip Url for Twitch
     */
    public function getClipUrl(): string
    {
        // old ui, but less buggy
        return "https://clips.twitch.tv/{$this->twitch_id}";
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        return $this->belongsToMany(Compilation::class, 'compilation_clip')
            ->using(CompilationClip::class)
            ->withPivot(CompilationClip::getPivotColumns())
            ->withTimestamps();
    }

    /**
     * @internal this will not work without CompilationClip relationship being loaded (required for filament)
     *
     * @return BelongsTo<User, $this>
     */
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * @internal this will not work without CompilationClip relationship being loaded (required for filament)
     *
     * @return BelongsTo<User, $this>
     */
    public function adder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
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
            return $query->whereRaw(DB::raw('1 = 0'));
        }

        // Make sure to sort the rules in a way that allows the biggest scope to filter the most first
        return $query
            ->whereNotArchived()
            ->whereSubmittedAfter(now()->sub($maxAge))
            ->whereBroadcasterGavePermission()
            ->whereNotBlocked()
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
    protected function whereNotBlocked(Builder $query): Builder
    {
        return $query->whereNot('status', ClipStatus::Blocked);
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
        if (empty($query->getQuery()->columns)) {
            $query->addSelect($query->getModel()->getTable().'.*');
        }

        return $query->selectSub(
            Vote::query()
                ->selectRaw('COALESCE(final_jury_votes + final_public_votes,COUNT(*), 0)')
                ->whereColumn('clip_id', 'clips.id')
                ->where('voted', true),
            'absolute_votes'
        );
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
                ->selectRaw('COALESCE(final_score,SUM(CASE WHEN type = ?::integer THEN ?::integer ELSE ?::integer END), 0)', [ClipVoteType::Jury->value, $juryWeight, $publicWeight])
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
        if (empty($query->getQuery()->columns)) {
            $query->addSelect($query->getModel()->getTable().'.*');
        }

        return $query->selectSub(
            Vote::query()
                ->selectRaw('COALESCE(final_public_votes,COUNT(*), 0)')
                ->whereColumn('clip_id', 'clips.id')
                ->where('type', ClipVoteType::Public)
                ->where('voted', true),
            'public_votes'
        );
    }

    /**
     * Counts jury votes as `jury_votes`.
     */
    #[Scope]
    protected function withJuryVoteCount(Builder $query): Builder
    {
        if (empty($query->getQuery()->columns)) {
            $query->addSelect($query->getModel()->getTable().'.*');
        }

        return $query->selectSub(
            Vote::query()
                ->selectRaw('COALESCE(final_jury_votes,COUNT(*), 0)')
                ->whereColumn('clip_id', 'clips.id')
                ->where('type', ClipVoteType::Jury)
                ->where('voted', true),
            'jury_votes'
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
