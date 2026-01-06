<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    public $incrementing = false;

    public const PLACEHOLDER_BOX_ART = "https://static-cdn.jtvnw.net/ttv-static/404_boxart-{width}x{height}.jpg";

    public function clips(): HasMany
    {
        return $this->hasMany(Clip::class, 'game_id', 'id');
    }

    public function getBoxArt($width = 188, $height = 250): ?string
    {
        $boxArtUrl = $this->box_art ?? self::PLACEHOLDER_BOX_ART;

        return str_replace(['{width}', '{height}'], [$width, $height], $boxArtUrl);
    }
}
