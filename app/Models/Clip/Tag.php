<?php

namespace App\Models\Clip;

use App\Models\Clip;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\Clip\TagFactory> */
    use HasFactory;

    public function clips(): BelongsToMany
    {
        return $this->belongsToMany(Clip::class,'clip_tags');
    }
}
