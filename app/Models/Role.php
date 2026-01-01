<?php

namespace App\Models;

use App\Enums\Permission;
use App\Policies\RolePolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

#[UsePolicy(RolePolicy::class)]
class Role extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Get all permissions for this role
     * @return array<int, Permission>
     */
    public function permissions(): array
    {
        return DB::table('role_permissions')
            ->where('role_id', $this->id)
            ->pluck('permission')
            ->map(fn(string $perm) => Permission::tryFrom($perm))
            ->filter()
            ->toArray();
    }
}
