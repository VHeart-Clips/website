<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\TwitchClipThumbnailCast;
use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Clips\ClipStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\ExternalContentProxyType;
use App\Enums\FeatureFlag;
use App\Http\Resources\PublicClipResource;
use App\Models\Contracts\ExternalProxyable;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\Traits\Auditable;
use App\Models\Traits\Clip\ClipRelationships;
use App\Models\Traits\Clip\ClipToClipCompilationRelationships;
use App\Models\Traits\Clip\Scopes\ClipArchiveScopes;
use App\Models\Traits\Clip\Scopes\ClipVoteScopes;
use App\Models\Traits\HasExternalProxy;
use App\Models\Traits\Reportable;
use App\Policies\ClipPolicy;
use App\Support\FeatureFlag\Feature;
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
    use ClipArchiveScopes;
    use ClipRelationships;
    use ClipToClipCompilationRelationships;
    use ClipVoteScopes;
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

    private function extractUserIdFromParameter(User|int $userOrId): int
    {
        return $userOrId instanceof User ? $userOrId->id : $userOrId;
    }
}
