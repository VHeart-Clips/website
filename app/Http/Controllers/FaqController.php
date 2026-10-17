<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Faq\FaqEntry;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FaqController extends Controller
{
    public function __invoke(Request $request): View
    {
        $isSearch = $request->filled('search');

        $questions = FaqEntry::query()
            ->when($isSearch, function (Builder $query) use ($request): void {
                $locale = app()->getLocale();
                $searchTerm = '%'.$request->input('search').'%';

                // search via database may give a different result than pure clientside one because database will
                // return anything that contains searchTerm, not just visible text.
                // but this is good enough as a fallback method
                $query->where(function (Builder $q) use ($locale, $searchTerm): void {
                    $q->whereLike('title->'.$locale, $searchTerm)
                        ->orWhereLike('body->'.$locale, $searchTerm);
                });
            })
            ->whereNowOrPast('published_at')
            ->orderBy('order')
            ->whereLocale('title', app()->getLocale())
            ->get();

        return view('faq', [
            'questions' => $questions,
            'isSearch' => $isSearch,
            // no need for schema if we disallow indexing anyway
            'schema' => $isSearch ? null : $this->getFaqSchema($questions),
        ]);
    }

    /**
     * @param  Collection<FaqEntry>  $questions
     */
    private function getFaqSchema(Collection $questions): string
    {
        try {
            return Cache::remember('faq-schema', now()->addHour(), static fn (): string => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'dateModified' => $questions->max('updated_at')?->toIso8601String(),
                'mainEntity' => $questions->map(fn (FaqEntry $entry): array => $entry->getSchemaFormat())->values(),
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG));
        } catch (Exception $exception) {
            report($exception);

            return '[]';
        }
    }
}
