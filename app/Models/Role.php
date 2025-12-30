<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use RuntimeException;

class Role extends Model
{
    protected $guarded = [];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'user_roles');
    } 
}
