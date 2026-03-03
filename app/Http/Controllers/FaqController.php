<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Faq\FaqEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $questions = FaqEntry::query()
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
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

        return view('faq', ['questions' => $questions]);
    }
}
