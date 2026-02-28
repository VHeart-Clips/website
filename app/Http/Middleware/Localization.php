<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        $availableLocales = config('app.locales', []);

        // TODO: should we store that in the database?
        /*
        $user = $request->user();
        if ($user && ($userLang = $user->language)) {
            if (array_key_exists($userLang, $availableLocales)) {
                session()->put('locale', $userLang);
            }
        }
        */

        // If the session has a valid locale, apply it and exit immediately.
        if (session()?->has('locale')) {
            $locale = session('locale');

            if (array_key_exists((string) $locale, $availableLocales)) {
                app()->setLocale($locale);
                $request->setLocale($locale);

                return $next($request);
            }
        }

        // Try guessing based on browser headers
        $locale = $request->getPreferredLanguage(array_keys($availableLocales));

        if ($locale) {
            app()->setLocale($locale);
            $request->setLocale($locale);
        }

        return $next($request);
    }
}
