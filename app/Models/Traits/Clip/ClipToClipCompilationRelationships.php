<?php

declare(strict_types=1);

namespace App\Models\Traits\Clip;

use App\Models\Clip;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Clip
 */
trait ClipToClipCompilationRelationships
{
    /**
     * @internal this will not work without CompilationClip relationship being loaded (required for filament)
     *
     * @return BelongsTo<User, $this>
     */
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * @internal this will not work without CompilationClip relationship being loaded (required for filament)
     *
     * @return BelongsTo<User, $this>
     */
    public function adder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
