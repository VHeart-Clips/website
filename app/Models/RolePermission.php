<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermission extends Pivot
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'role_permissions';

    protected $casts = [
        'permission' => Permission::class,
    ];
}
