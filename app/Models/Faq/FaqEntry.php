<?php

declare(strict_types=1);

namespace App\Models\Faq;

use App\Http\Resources\FaqEntryResource;
use App\Models\Traits\Auditable;
use App\Policies\FaqEntryPolicy;
use Database\Factories\Faq\FaqEntryFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

#[UsePolicy(FaqEntryPolicy::class)]
#[UseResource(FaqEntryResource::class)]
class FaqEntry extends Model
{
    use Auditable;

    /** @use HasFactory<FaqEntryFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    public array $translatable = [
        'title',
        'body',
    ];

    public function getSchemaFormat(): array
    {
        $html = Str::markdown($this->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]);

        return [
            '@type' => 'Question',
            'name' => $this->title,
            'dateModified' => $this->updated_at?->toIso8601String(),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                // schema only allows a limited amount of html tags so we just strip anything else
                'text' => mb_trim(strip_tags($html, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'br', 'ol', 'ul', 'li', 'a', 'p', 'div', 'b', 'strong', 'i', 'em'])),
            ],
        ];
    }

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
