<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserSettings\LanguagePreference;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        if (filled($locale) && $locale !== app()->currentLocale()) {
            app()->setLocale($locale);
            $request->setLocale($locale);
        }

        return $next($request);
    }

    private function resolveLocale(Request $request): ?string
    {
        // we check if the user has any language preference configured
        $preference = $request->user()?->language;

        if ($preference instanceof LanguagePreference) {
            return $preference->value;
        }

        // if not, we check the session for a configured locale for guests
        if ($request->hasSession()) {
            $sessionLocale = LanguagePreference::tryFrom((string) $request->session()->get('locale'));

            if ($sessionLocale !== null) {
                return $sessionLocale->value;
            }
        }

        // if we still do not have anything we just guess the locale based on the browser accepted languages
        // and just select the first language we support (if any)
        foreach ($request->getLanguages() as $language) {
            $match = LanguagePreference::tryFrom($language)
                ?? LanguagePreference::tryFrom(mb_strtolower(array_first(explode('_', $language))));

            if ($match instanceof LanguagePreference) {
                return $match->value;
            }
        }

        return null;
    }
}
