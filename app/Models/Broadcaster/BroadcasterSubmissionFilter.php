<?php

declare(strict_types=1);

namespace App\Models\Broadcaster;

use App\Models\Category;
use App\Models\User;
use Database\Factories\Broadcaster\BroadcasterSubmissionFilterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BroadcasterSubmissionFilter extends Model
{
    /** @use HasFactory<BroadcasterSubmissionFilterFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return BelongsTo<Broadcaster, $this>
     */
    public function broadcaster(): BelongsTo
    {
        return $this->belongsTo(Broadcaster::class);
    }

    /**
     * @return MorphTo<User|Category|Model, $this>
     */
    public function filterable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'state' => 'boolean',
        ];
    }
}
