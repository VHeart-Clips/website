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
        );

        if ($lang === null) {
            if (! $request->expectsJson()) {
                return back()->withErrors([
                    'locale' => 'Invalid locale selected',
                ]);
            }

            abort(422);
        }

        app()->setLocale($lang->value);
        $request->session()?->put('locale', $lang->value);

        $request->user()?->update([
            'language' => $lang,
        ]);

        if ($request->expectsJson()) {
            return new JsonResponse([
                'message' => __('Ok!'),
            ], 200);
        }

        return back();
    }
}
