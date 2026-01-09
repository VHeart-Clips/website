<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Permission;
use App\Policies\RolePolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UsePolicy(RolePolicy::class)]
class Role extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }
}
