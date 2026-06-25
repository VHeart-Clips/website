<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories;

use App\Actions\ImportCategoryAction;
use App\Models\Category;
use App\Services\Twitch\Data\CategoryDto;
use App\Services\Twitch\Data\GameDto;
use App\Services\Twitch\Enums\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class CategorySelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('filament/inputs/category-select.label')
            ->translateLabel()
            ->searchable()
            ->allowHtml()
            ->getSearchResultsUsing(function (string $search, TwitchService $twitchService): array {
                if (blank($search)) {
                    return [];
                }

                $search = mb_trim($search);
                $lowercaseSearch = mb_strtolower($search);

                $twitchQuery = collect(explode(' ', $lowercaseSearch))->unique()->sort()->values();
                $twitchQueryCacheKey = self::class.':query:'.sha1($twitchQuery->join(','));

                if (! RateLimiter::attempt(self::class.':ratelimit:'.auth()->id(), maxAttempts: 20, callback: static fn (): true => true)) {
                    Notification::make('rate-limited')
                        ->warning()
                        ->title(__('filament/inputs/category-select.errors.rate-limited'))
                        ->send();

                    $twitchResults = collect();
                } else {
                    try {
                        $twitchResults = collect(Cache::remember($twitchQueryCacheKey, now()->addDay(), static fn () => retry(
                            times: 3,
                            callback: static fn (): array => $twitchService->asSessionUser()->searchCategories($twitchQuery->join(' '), 100),
                            sleepMilliseconds: 200,
                            when: static fn (Throwable $e): bool => $e instanceof ConnectionException,
                        )));

                        $twitchResults->each(fn (CategoryDto $dto) => Cache::put("twitch:category:$dto->id", $dto, now()->addMinutes(30)));
                    } catch (Throwable $e) {
                        report($e);
                        $twitchResults = collect();
                    }
                }

                $localQuery = Category::query()
                    ->whereNot('id', 0)
                    ->where('title', 'ilike', "%$search%")
                    ->orderByRaw('CASE WHEN lower(title) LIKE lower(?) THEN 0 ELSE 1 END', [$search.'%'])
                    ->limit(100)
                    ->get()
                    ->toBase();

                return $localQuery
                    ->merge($twitchResults)
                    ->sortBy(function (Category|CategoryDto|GameDto $item) use ($lowercaseSearch): float {
                        $title = mb_strtolower($item instanceof Category ? $item->title : $item->name);

                        similar_text($lowercaseSearch, $title, $percent);
                        $prefixBonus = Str::startsWith($title, $lowercaseSearch) ? 50 : 0;

                        return -($percent + $prefixBonus);
                    })
                    ->take(15)
                    ->mapWithKeys(fn (Category|CategoryDto|GameDto $category): array => [
                        $category->id => $this->buildOptionHtml($category),
                    ])
                    ->all();
            })
            ->getOptionLabelUsing(function (string $value, TwitchService $twitchService, ImportCategoryAction $importCategoryAction): ?string {
                $resolved = $this->resolveCategory((int) $value, $twitchService, $importCategoryAction);

                if (! $resolved || $resolved->id === 0) {
                    return null;
                }

                return $this->buildOptionHtml($resolved);
            });
    }

    protected function resolveCategory(int $id, TwitchService $twitchService, ImportCategoryAction $importCategoryAction): ?Category
    {
        if ($id === 0) {
            return null;
        }

        if ($category = Category::find($id)) {
            return $category;
        }

        /** @var CategoryDto|GameDto|null $dto */
        if ($dto = Cache::get("twitch:category:$id")) {
            return $importCategoryAction->execute($dto);
        }

        try {
            $dto = retry(
                times: 3,
                callback: static fn (): ?GameDto => array_first($twitchService->collection(TwitchEndpoints::GetGames, ['id' => $id])),
                sleepMilliseconds: 200,
                when: static fn (Throwable $e): bool => $e instanceof ConnectionException,
            );

            return $importCategoryAction->execute($dto);
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    protected function buildOptionHtml(Category|CategoryDto|GameDto|null $category): string
    {
        $isModel = $category instanceof Category;

        $name = e($isModel ? $category->title : $category->name);
        $imageUrl = Str::replace(['{width}', '{height}'], [18, 24], $isModel ? $category->box_art : $category->boxArtUrl);

        $img = "<img src='$imageUrl' loading='lazy' class='h-6 w-4.5 object-cover rounded-sm' alt='$name'/>";

        return "<div class='flex items-center gap-2'>$img<span class='font-medium'>$name</span></div>";
    }
}
