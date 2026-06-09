<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BanFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Kirschbaum\Commentions\HasComments;

class Ban extends Model
{
    use HasComments;

    /** @use HasFactory<BanFactory> */
    use HasFactory;

    public function bannable(): MorphTo
    {
        return $this->morphTo()
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id')
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function unbannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unbanned_by')
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function isActive(): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        if ($this->isPermanent()) {
            return true;
        }

        return $this->banned_until->isFuture();
    }

    public function isPermanent(): bool
    {
        return is_null($this->banned_until);
    }

    public function isExpired(): bool
    {
        if ($this->banned_until?->isNowOrPast()) {
            return true;
        }

        return $this->unbanned_at?->isNowOrPast() ?? false;
    }

    #[Scope]
    public function wherePermanent(Builder $query): Builder
    {
        return $query->whereNull('banned_until');
    }

    #[Scope]
    public function whereTemporary(Builder $query): Builder
    {
        return $query->whereNotNull('banned_until');
    }

    #[Scope]
    protected function whereActive(Builder $query): Builder
    {
        return $query
            ->whereNull('unbanned_at')
            ->where(fn (Builder $q): Builder => $q
                ->whereNull('banned_until')
                ->orWhere('banned_until', '>', now())
            );
    }

    #[Scope]
    protected function whereExpired(Builder $query): Builder
    {
        return $query->where(fn (Builder $q): Builder => $q
            ->whereNotNull('unbanned_at')
            ->orWhere('banned_until', '<=', now())
        );
    }

    protected function casts(): array
    {
        return [
            'banned_until' => 'datetime',
            'unbanned_at' => 'datetime',
        ];
    }
}
