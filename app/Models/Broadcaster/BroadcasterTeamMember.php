<?php

declare(strict_types=1);

namespace App\Models\Broadcaster;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\FeatureFlag;
use App\Models\Traits\Auditable;
use App\Models\User;
use App\Policies\Broadcaster\BroadcasterTeamMemberPolicy;
use App\Support\FeatureFlag\Feature;
use Database\Factories\Broadcaster\BroadcasterTeamMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[UsePolicy(BroadcasterTeamMemberPolicy::class)]
class BroadcasterTeamMember extends Model
{
    use Auditable;

    /** @use HasFactory<BroadcasterTeamMemberFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Broadcaster, $this>
     */
    public function broadcaster(): BelongsTo
    {
        return $this->belongsTo(Broadcaster::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        // We have to manually sort them as json will preserve order of simple arrays rip
        // technically it doesnt matter but doesnt hurt either
        static::saving(static function (BroadcasterTeamMember $broadcasterTeamMember): void {
            if ($broadcasterTeamMember->permissions) {
                $broadcasterTeamMember->permissions = $broadcasterTeamMember->permissions
                    ->sortBy(fn (BroadcasterPermission $enum) => $enum->value)
                    ->values();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'permissions' => AsEnumCollection::of(BroadcasterPermission::class),
        ];
    }

    #[Scope]
    protected function whereHasPermission(Builder $query, BroadcasterPermission|Collection|array|null $permissions = null)
    {
        if (! Feature::isActive(FeatureFlag::BroadcasterTenant)) {
            return $query->whereRaw(DB::raw('1 = 0'));
        }

        if (! $permissions) {
            return $query->whereJsonLength('permissions', '>', '0');
        }

        if ($permissions instanceof BroadcasterPermission) {
            $permissions = [$permissions];
        }

        return $query->whereJsonContains('permissions', $permissions);
    }
}
