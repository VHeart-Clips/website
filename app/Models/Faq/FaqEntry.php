<?php

declare(strict_types=1);

namespace App\Models\Faq;

use App\Http\Resources\FaqEntryResource;
use App\Policies\FaqEntryPolicy;
use Database\Factories\Faq\FaqEntryFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[UsePolicy(FaqEntryPolicy::class)]
#[UseResource(FaqEntryResource::class)]
class FaqEntry extends Model
{
    /** @use HasFactory<FaqEntryFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    public array $translatable = [
        'title',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'json:unicode',
            'body' => 'json:unicode',
            'links' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
