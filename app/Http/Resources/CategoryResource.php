<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'art' => [
                'small' => $this->proxiedContentUrl(144, 192),
                'medium' => $this->proxiedContentUrl(285, 380),
                'large' => $this->proxiedContentUrl(600, 800),
            ],
        ];
    }
}
