<?php

namespace App\Models;

use App\Policies\ClipPolicy;
use Database\Factories\ClipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UsePolicy(ClipPolicy::class)]
class Clip extends Model
{
    /** @use HasFactory<ClipFactory> */
    use HasFactory;

    public function broadcaster(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'id', 'broadcaster_id')
            ->withDefault(['name' => "Unknown"]);
    }

    public function creator(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'id', 'creator_id')
            ->withDefault(['name' => "Unknown"]);
    }

    public function submitter(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'id', 'submitter_id')
            ->withDefault(['name' => "Unknown"]);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'id', 'game_id')
            ->withDefault(['title' => 'Unknown']);
    }
}
