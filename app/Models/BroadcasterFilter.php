<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BroadcasterFilter extends Model
{
    use HasFactory;
    public $timestamps = false;

    /**
     * @return BelongsTo<User, $this>
     */
    public function broadcaster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broadcaster_id');
    }

    public function filterable(): MorphTo
    {
        return $this->morphTo();
    }
}
