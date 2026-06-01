<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips;

use App\Models\Clip;
use App\Services\Twitch\TwitchService;
use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Str;

class ClipSelect extends Select
{
    protected ?Closure $modifyClipQueryUsingClosure = null;

    protected int|Closure $optionsLimit = 10;

    protected int $clipTitleLengthLimit = 80;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('filament/inputs/clip-select.label')
            ->translateLabel()
            ->searchable()
            ->allowHtml()
            ->options(function (): array {
                if (! $this->isPreloaded) {
                    return [];
                }

                return $this->resolveClipQuery()
                    ->get()
                    ->toBase()
                    ->mapWithKeys(fn (Clip $clip): array => [$clip->id => $this->buildOptionHtml($clip)])
                    ->all();
            })
            ->getSearchResultsUsing(function (string $search, TwitchService $twitchService): array {
                if (blank($search)) {
                    return [];
                }

                $search = mb_trim($search);

                if ($clipId = $twitchService->parseClipId($search)) {
                    $search = $clipId;
                }

                $lowercaseSearch = mb_strtolower($search);

                return $this->resolveClipQuery($search)
                    ->get()
                    ->toBase()
                    ->sortBy(function (Clip $clip) use ($search, $lowercaseSearch): float {
                        if ($clip->twitch_id === $search) {
                            return -10000;
                        }

                        similar_text($lowercaseSearch, $clip->title, $percent);
                        $prefixBonus = Str::startsWith($clip->title, $lowercaseSearch) ? 50 : 0;

                        return -($percent + $prefixBonus);
                    })
                    ->mapWithKeys(fn (Clip $clip): array => [
                        $clip->id => $this->buildOptionHtml($clip),
                    ])
                    ->all();
            })
            ->getOptionLabelUsing(function (int|string $value): ?string {
                $resolved = $this->resolveClip($value);

                if (! $resolved instanceof Clip) {
                    return null;
                }

                return $this->buildOptionHtml($resolved);
            });
    }

    /**
     * @param  Closure(EloquentBuilder<Clip>, string|null $search): (EloquentBuilder<Clip>|null)  $callback
     */
    public function modifyClipQueryUsing(Closure $callback): static
    {
        $this->modifyClipQueryUsingClosure = $callback;

        return $this;
    }

    public function maxClipTitleLength(int $value = 80): static
    {
        $this->clipTitleLengthLimit = $value;

        return $this;
    }

    protected function resolveClip(int|string $id): ?Clip
    {
        $query = Clip::query();

        if ($this->modifyClipQueryUsingClosure instanceof Closure) {
            $query = $this->evaluate($this->modifyClipQueryUsingClosure, ['query' => $query, 'search' => null]) ?? $query;
        }

        return $query->find($id);
    }

    protected function resolveClipQuery(?string $search = null): EloquentBuilder
    {
        $query = Clip::query()
            ->when(filled($search), fn (EloquentBuilder $builder) => $builder
                ->where(fn (Builder $builder) => $builder
                    ->where('title', 'ilike', "%$search%")
                    ->orWhere('twitch_id', $search)
                )
                ->orderByRaw('CASE WHEN lower(title) LIKE lower(?) THEN 0 ELSE 1 END', [$search.'%'])
            )
            ->limit($this->getOptionsLimit());

        if ($this->modifyClipQueryUsingClosure instanceof Closure) {
            return $this->evaluate($this->modifyClipQueryUsingClosure, ['query' => $query, 'search' => $search]) ?? $query;
        }

        return $query;
    }

    protected function buildOptionHtml(Clip $clip): string
    {
        $name = e(Str::limit($clip->title, $this->clipTitleLengthLimit));

        $img = "<img src='$clip->thumbnail_url' loading='lazy' class='h-6 aspect-video object-cover rounded-sm' alt='$name'/>";

        return "<div class='flex items-center gap-2'>$img<span class='font-medium'>$name</span></div>";
    }
}
