<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Filament\PanelProvider as AbstractFilamentPanelProvider;
use SocialiteProviders\Manager\OAuth2\AbstractProvider as AbstractSocialiteProvider;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
 * Architecture Tests to enforce a certain level of consistency (and quality)
 */
arch('only traits in traits folder')
    ->expect('App\*\Traits')
    ->toBeTraits();

// It avoids the usage of die, var_dump, and similar functions, and ensures we are not using deprecated PHP functions.
// https://github.com/pestphp/pest/blob/4.x/src/ArchPresets/Php.php
arch()->preset()->php();

// It ensures we are not using code that could lead to security vulnerabilities.
// We may use sha1 for cache keys though
// https://github.com/pestphp/pest/blob/4.x/src/ArchPresets/Security.php
arch()->preset()->security()->ignoring('sha1');

// It ensures the projects structure is following the well-known Laravel conventions
// https://github.com/pestphp/pest/blob/4.x/src/ArchPresets/Laravel.php
arch()->preset()->laravel()
    ->ignoring([
        "App\Providers\Filament", // Filament has different base class
        "App\Providers\Socialite", // Custom Socialite Providers
        "App\Services", // Services may not follow the strict laravel conventions (yet)
    ]);

// Filament
arch('filament specifics')->expect('App\Providers\Filament')
    ->toHaveSuffix('PanelProvider')
    ->toExtend(AbstractFilamentPanelProvider::class);

// Socialite Providers
arch('socialite specifics')->expect('App\Providers\Socialite')
    ->toHaveSuffix('SocialiteProvider')
    ->toExtend(AbstractSocialiteProvider::class);

// Services
arch()
    ->expect('App\Services')
    ->not->toUse('App\Http\Controllers')
    ->not->toUse('App\Http\Requests')
    ->not->toUse(['dd', 'dump', 'ray', 'var_dump']);
arch()
    ->expect('App\Services\*\Data')
    ->classes()
    ->toHaveSuffix('Dto')
    ->toBeReadonly()
    ->toExtendNothing();
arch()
    ->expect('App\Services\*\Exceptions')
    ->classes()
    ->toHaveSuffix('Exception')
    ->toExtend(Exception::class);
