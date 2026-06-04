<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Ban;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 */
trait Bannable
{
    /**
     * @return MorphMany<Ban, $this>
     */
    public function bans(): MorphMany
    {
        return $this->morphMany(Ban::class, 'bannable');
    }

    public function getBan(): ?Ban
    {
        return $this->bans()
            ->whereActive()
            // we just let the longest ban take priority
            ->orderByDesc('banned_until')
            ->first();
    }

    public function isBanned(): bool
    {
        return $this->bans()
            ->whereActive()
            ->exists();
    }

    #[Scope]
    public function whereBanned(Builder $query): Builder
    {
        return $query->whereHas('bans', fn (Builder $q): Builder => $q->whereActive());
    }

    #[Scope]
    public function whereBannedBy(Builder $query, User|int $user): Builder
    {
        return $query->whereHas('bans', fn (Builder $q): Builder => $q
            ->where('admin_id', $user instanceof User ? $user->id : $user)
        );
    }

    #[Scope]
    public function whereUnbannedBy(Builder $query, User|int $user): Builder
    {
        return $query->whereHas('bans', fn (Builder $q): Builder => $q
            ->where('unbanned_by', $user instanceof User ? $user->id : $user)
        );
    }

    #[Scope]
    public function whereNotBanned(Builder $query): Builder
    {
        return $query->whereDoesntHave('bans', fn (Builder $q): Builder => $q->whereActive());
    }

    #[Scope]
    public function wherePermanentlyBanned(Builder $query): Builder
    {
        return $query->whereHas('bans', fn (Builder $q): Builder => $q->whereActive()->wherePermanent());
    }

    #[Scope]
    public function whereTemporarilyBanned(Builder $query): Builder
    {
        return $query->whereHas('bans', fn (Builder $q): Builder => $q->whereActive()->whereTemporary());
    }
}
