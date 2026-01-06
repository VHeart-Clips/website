<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Clip\Tag;
use App\Policies\ClipPolicy;
use Database\Factories\ClipFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[UsePolicy(ClipPolicy::class)]
class Clip extends Model
{
    /** @use HasFactory<ClipFactory> */
    use HasFactory;

    public function broadcaster(): BelongsTo
    {
        return $this->BelongsTo(User::class)
            ->withDefault(['name' => 'N/A']);
    }

    public function creator(): BelongsTo
    {
        return $this->BelongsTo(User::class)
            ->withDefault(['name' => 'N/A']);
    }

    public function submitter(): BelongsTo
    {
        return $this->BelongsTo(User::class)
            ->withDefault(['name' => 'N/A']);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class)
            ->withDefault(['title' => 'N/A']);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'clip_tags');
    }
}
