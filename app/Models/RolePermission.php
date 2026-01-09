<?php

namespace App\Models;

use App\Enums\Permission;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermission extends Pivot
{
    public $timestamps = false;
    protected $table = 'role_permissions';
    protected $casts = [
        'permission' => Permission::class,
    ];
}
