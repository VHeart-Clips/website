<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\TwitchClipThumbnailCast;
use App\Enums\Clips\ClipStatus;
use App\Enums\ExternalContentProxyType;
use App\Http\Resources\PublicClipResource;
use App\Models\Contracts\ExternalProxyable;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use App\Models\Traits\Auditable;
use App\Models\Traits\Clip\ClipRelationships;
use App\Models\Traits\Clip\ClipToClipCompilationRelationships;
use App\Models\Traits\Clip\Scopes\ClipArchiveScopes;
use App\Models\Traits\Clip\Scopes\ClipFilterScopes;
use App\Models\Traits\Clip\Scopes\ClipVoteScopes;
use App\Models\Traits\HasExternalProxy;
use App\Models\Traits\Reportable;
use App\Policies\ClipPolicy;
use Database\Factories\ClipFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

/**
 * @property int $id
 */
#[ScopedBy(ClipPermissionScope::class)]
#[ScopedBy(ClipWithoutBannedCategoryScope::class)]
#[UseResource(PublicClipResource::class)]
#[UsePolicy(ClipPolicy::class)]
class Clip extends Model implements Commentable, ExternalProxyable
{
    use Auditable;
    use ClipArchiveScopes;
    use ClipFilterScopes;
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

    private function extractUserIdFromParameter(User|int $userOrId): int
    {
        return $userOrId instanceof User ? $userOrId->id : $userOrId;
    }
}
