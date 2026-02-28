<?php

declare(strict_types=1);

namespace App\Models\Clip;

use App\Http\Resources\Clip\TagResource;
use App\Models\Clip;
use App\Policies\TagPolicy;
use Database\Factories\Clip\TagFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Translatable\HasTranslations;

#[UsePolicy(TagPolicy::class)]
#[UseResource(TagResource::class)]
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var bool */
    public $timestamps = false;

    public array $translatable = [
        'name',
    ];

    /**
     * @return BelongsToMany<Clip, $this, Pivot>
     */
    public function clips(): BelongsToMany
    {
        return $this->belongsToMany(Clip::class, 'clip_tags');
    }

    protected function casts(): array
    {
        return [
            'name' => 'json:unicode',
        ];
    }
}
