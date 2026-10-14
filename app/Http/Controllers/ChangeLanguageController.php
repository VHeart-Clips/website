<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserSettings\LanguagePreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChangeLanguageController extends Controller
{
    public function __invoke(Request $request): JsonResponse|RedirectResponse
    {
        $lang = $request->enum(
            'locale',
            LanguagePreference::class,
            LanguagePreference::tryFrom(app()->getFallbackLocale())
        );

        if ($lang === null) {
            if (! $request->expectsJson()) {
                return redirect()->back()->withErrors([
                    'locale' => 'Invalid locale selected',
                ]);
            }

            abort(422);
        }

        app()->setLocale($lang->value);
        session()?->put('locale', $lang->value);

        if ($request->expectsJson()) {
            return new JsonResponse([
                'message' => __('Ok!'),
            ], 200);
        }

        return redirect()->back();
    }
}
