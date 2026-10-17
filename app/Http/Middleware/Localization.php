<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserSettings\LanguagePreference;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = array_column(LanguagePreference::cases(), 'value');

        // TODO: should we store that in the database?
        /*
        $user = $request->user();
        if ($user && ($userLang = $user->language)) {
            if (in_array($userLang, $availableLocales, true)) {
                session()->put('locale', $userLang);
            }
        }
        */

        // If the session has a valid locale, apply it and exit immediately.
        if (session()?->has('locale')) {
            $locale = session('locale');

            if (in_array($locale, $availableLocales, true)) {
                app()->setLocale($locale);
                $request->setLocale($locale);

                return $next($request);
            }
        }

        // Try guessing based on browser headers
        $locale = $request->getPreferredLanguage($availableLocales);

        if ($locale) {
            app()->setLocale($locale);
            $request->setLocale($locale);
        }

        return $next($request);
    }
}
