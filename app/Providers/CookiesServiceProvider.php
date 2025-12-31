<?php

namespace App\Providers;

use Whitecube\LaravelCookieConsent\Consent;
use Whitecube\LaravelCookieConsent\Cookie;
use Whitecube\LaravelCookieConsent\CookiesServiceProvider as ServiceProvider;
use Whitecube\LaravelCookieConsent\Facades\Cookies;

class CookiesServiceProvider extends ServiceProvider
{
    /**
     * Define the cookies users should be aware of.
     */
    protected function registerCookies(): void
    {
        // Register Laravel's base cookies under the "required" cookies section:
        Cookies::essentials()
            ->session()
            ->csrf();

        Cookies::optional()
            ->cookie(function(Cookie $cookie) {
                $cookie->name('appearance')
                ->description(__('cookies.appearance.description'))
                ->duration(60 * 24 * 365);
            })
            ->cookie(function(Cookie $cookie) {
                $cookie->name('sidebar_state')
                ->description(__('cookies.sidebar_state.description'))
                ->duration(60 * 24 * 365);
            });


        // Register all Analytics cookies at once using one single shorthand method:
        // Cookies::analytics()
        //    ->google(
        //         id: config('cookieconsent.google_analytics.id'),
        //         anonymizeIp: config('cookieconsent.google_analytics.anonymize_ip')
        //    );

        // Register custom cookies under the pre-existing "optional" category:
        // Cookies::optional()
        //     ->name('darkmode_enabled')
        //     ->description('This cookie helps us remember your preferences regarding the interface\'s brightness.')
        //     ->duration(120)
        //     ->accepted(fn(Consent $consent, MyDarkmode $darkmode) => $consent->cookie(value: $darkmode->getDefaultValue()));
    }
}
