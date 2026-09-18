<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Permission;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Table(name: 'role_permissions')]
#[WithoutTimestamps]
class RolePermission extends Pivot
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'permission' => Permission::class,
        ];
    }
}
