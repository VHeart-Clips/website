<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\FaQ\FaqEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin FaqEntry
 */
class FaqEntryResource extends JsonResource
{
    public static $wrap;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var string $markdownText */
        $markdownText = $this->body;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => Str::markdown($markdownText, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
            'order' => $this->order ?? 0,
        ];
    }
}
