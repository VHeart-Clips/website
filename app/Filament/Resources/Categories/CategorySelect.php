<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories;

use App\Actions\ImportCategoryAction;
use App\Models\Category;
use App\Services\Twitch\Data\CategoryDto;
use App\Services\Twitch\Data\GameDto;
use App\Services\Twitch\Enums\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Override;

class CategorySelect extends Select
{
    protected ?Closure $whereNotExists = null;

    protected ?Closure $ignoredIds = null;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->getSearchResultsUsing(function (string $search, TwitchService $twitchService) {
            $search = mb_trim($search);
            $categories = collect($twitchService->asSessionUser()->searchCategories($search, 100))
                ->each(fn (CategoryDto $category) => Cache::put("twitch:category:$category->id", $category, now()->addMinutes(30)))
                ->map(fn (CategoryDto $item): array => ['title' => $item->name, 'id' => $item->id]);

            $categoryQuery = Category::where('title', 'ilike', "%$search%");

            if ($this->whereNotExists) {
                $categoryQuery->whereNotExists(function (Builder $query): void {
                    $this->evaluate($this->whereNotExists, ['query' => $query]);
                });
            }

            $category = $categoryQuery->limit(5)
                ->pluck('title', 'id')
                ->map(fn (string $title, int $id): array => ['id' => $id, 'title' => $title])
                ->merge($categories)
                ->unique('id')
                ->take(100);

            $existingIds = [];

            if ($this->ignoredIds) {
                $existingIds = $this->evaluate($this->ignoredIds, [
                    'category' => $category,
                ]);
            }

            return $category->reject(fn (array $item): bool => in_array((string) $item['id'], $existingIds, true))
                ->values()
                ->sortBy(fn (array $item): int => levenshtein(mb_strtolower($search), mb_strtolower((string) $item['title'])))
                ->mapWithKeys(fn (array $item): array => [$item['id'] => $item['title']]);
        })
            ->getOptionLabelUsing(function (string $value, TwitchService $twitchService, ImportCategoryAction $importCategoryAction) {
                if ($title = Category::find((int) $value)?->title) {
                    return $title;
                }

                if ($category = Cache::get("twitch:category:$value")) {
                    $category = $importCategoryAction->execute($category);

                    return $category->title;
                }

                $categories = $twitchService->collection(TwitchEndpoints::GetGames, [
                    'id' => $value,
                ]);

                /** @var GameDto $game */
                $game = array_first($categories);

                $category = $importCategoryAction->execute($game);

                return $category->title;
            });
    }

    #[Override]
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->searchable();
    }

    public function whereNotExists(?Closure $callback): static
    {
        $this->whereNotExists = $callback;

        return $this;
    }

    public function ignoredIds(?Closure $callback): static
    {
        $this->ignoredIds = $callback;

        return $this;
    }
}
