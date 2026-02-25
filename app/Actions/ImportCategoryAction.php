<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Category;
use App\Services\Twitch\Data\CategoryDto;
use App\Services\Twitch\Data\GameDto;

class ImportCategoryAction
{
    public function execute(GameDto|CategoryDto $category): Category
    {
        return Category::firstOrCreate([
            'id' => $category->id,
        ], [
            'title' => $category->name,
            'box_art' => $category->box_art_url,
        ]);
    }
}
