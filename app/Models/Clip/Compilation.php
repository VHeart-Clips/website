<?php

declare(strict_types=1);

namespace App\Models\Clip;

use App\Enums\Clips\CompilationStatus;
use App\Enums\Clips\CompilationType;
use App\Models\Clip;
use App\Models\Contracts\FilamentResourceful;
use App\Models\User;
use App\Policies\CompilationPolicy;
use Database\Factories\Clip\CompilationFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

#[UsePolicy(CompilationPolicy::class)]
class Compilation extends Model implements Commentable, FilamentResourceful
{
    /** @use HasFactory<CompilationFactory> */
    use HasComments, HasFactory, SoftDeletes;

    public string $filamentResourcePageForCommentNotifications = 'edit';

    /**
     * User who has Created the Compilation (if available)
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Clip, $this, Pivot>
     */
    public function clips(): BelongsToMany
    {
        return $this->belongsToMany(Clip::class)
            ->using(CompilationClip::class)
            ->withPivot(CompilationClip::getPivotColumns())
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'status' => CompilationStatus::class,
            'type' => CompilationType::class,
            'claimed_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }
}
