<?php

declare(strict_types=1);

namespace App\Support\CookieConsent;

use Illuminate\Support\Facades\Cookie as CookieFacade;
use Symfony\Component\HttpFoundation\Cookie as CookieComponent;
use Whitecube\LaravelCookieConsent\CookiesManager;

class CustomCookiesManager extends CookiesManager
{
    protected function makeConsentCookie(): CookieComponent
    {
        return CookieFacade::make(
            name: config('cookieconsent.cookie.name'),
            value: json_encode($this->preferences, JSON_THROW_ON_ERROR),
            minutes: config('cookieconsent.cookie.duration'),
            domain: config('cookieconsent.cookie.domain'),
            secure: config('app.env') !== 'local',
            httpOnly: false,
        );
    }
}
