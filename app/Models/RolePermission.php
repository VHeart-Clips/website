<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Permission;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Table(name: 'role_permissions')]
class RolePermission extends Pivot
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'permission' => Permission::class,
    ];
}
