<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExternalContentProxyType;
use App\Http\Resources\CategoryResource;
use App\Models\Contracts\ExternalProxyable;
use App\Models\Traits\HasExternalProxy;
use App\Policies\CategoryPolicy;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UsePolicy(CategoryPolicy::class)]
#[UseResource(CategoryResource::class)]
class Category extends Model implements ExternalProxyable
{
    use HasExternalProxy;

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    public const string PLACEHOLDER_BOX_ART = 'https://static-cdn.jtvnw.net/ttv-static/404_boxart-{width}x{height}.jpg';

    public const array Defaults = [
        'title' => 'Pending Category',
        'is_banned' => false,
        'box_art' => self::PLACEHOLDER_BOX_ART,
    ];

    public $incrementing = false;

    public static function getProxyUrlColumn(): string
    {
        return 'box_art';
    }

    public static function getProxyExtension(): string
    {
        return 'jpg';
    }

    public static function supportsProxyDynamicSize(): bool
    {
        return true;
    }

    /**
     * @return HasMany<Clip, $this>
     */
    public function clips(): HasMany
    {
        return $this->hasMany(Clip::class, 'category_id', 'id');
    }

    public function getBoxArt(int $width = 188, int $height = 250): ?string
    {
        $boxArtUrl = $this->box_art ?? self::PLACEHOLDER_BOX_ART;

        return str_replace(['{width}', '{height}'], [$width, $height], $boxArtUrl);
    }

    public function getProxyType(): ExternalContentProxyType
    {
        return ExternalContentProxyType::TwitchCategory;
    }
}
