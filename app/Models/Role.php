<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Eloquent\SetOperator;
use App\Enums\Permission;
use App\Http\Resources\Role\RoleResource;
use App\Models\Traits\Auditable;
use App\Policies\RolePolicy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Spatie\Translatable\HasTranslations;

#[UseResource(RoleResource::class)]
#[UsePolicy(RolePolicy::class)]
class Role extends Model
{
    use Auditable;
    use HasFactory;
    use HasTranslations;

    public const int SUPERADMIN_ID = 0;

    public array $translatable = [
        'name',
        'desc',
    ];

    /**
     * @return BelongsToMany<User, $this, Pivot>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * @return HasMany<RolePermission, $this>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    public function getExtraAuditData(): array
    {
        return [
            'permissions' => $this->permissions()->pluck('permission')->map(fn (Permission $permission) => $permission->value)->toArray(),
        ];
    }

    /**
     * @param  Permission|Collection<int, Permission>|array<Permission>  $permissions
     */
    #[Scope]
    protected function whereHasPermission(
        Builder $query,
        Permission|Collection|array $permissions,
        SetOperator $operator = SetOperator::Any,
    ): Builder {
        $values = match (true) {
            $permissions instanceof Permission => [$permissions->value],
            $permissions instanceof Collection => $permissions->map(fn (Permission $p) => $p->value)->all(),
            default => array_map(static fn (Permission $p) => $p->value, $permissions),
        };

        $valueCount = count($values);

        return match ($operator) {
            SetOperator::Any => $query->where(fn (Builder $q): Builder => $q
                ->whereHas('permissions', fn (Builder $q): Builder => $q->whereIn('permission', $values))
                ->orWhere('id', self::SUPERADMIN_ID)
            ),
            SetOperator::AnyMissing => $query
                ->whereNot('id', self::SUPERADMIN_ID)
                ->whereHas('permissions', fn (Builder $q): Builder => $q->whereIn('permission', $values), '<', $valueCount),
            SetOperator::All => $query->where(fn (Builder $q): Builder => $q
                ->whereHas('permissions', fn (Builder $q): Builder => $q->whereIn('permission', $values), '>=', $valueCount)
                ->orWhere('id', self::SUPERADMIN_ID)
            ),
            SetOperator::None => $query
                ->whereNot('id', self::SUPERADMIN_ID)
                ->whereDoesntHave('permissions', fn (Builder $q): Builder => $q->whereIn('permission', $values)),
            SetOperator::Exact => $query
                ->whereNot('id', self::SUPERADMIN_ID)
                ->whereHas('permissions', fn (Builder $q): Builder => $q->whereIn('permission', $values), '>=', $valueCount)
                ->whereDoesntHave('permissions', fn (Builder $q): Builder => $q->whereNotIn('permission', $values)),
        };
    }

    protected function casts(): array
    {
        return [
            'name' => 'json:unicode',
            'desc' => 'json:unicode',
        ];
    }
}
