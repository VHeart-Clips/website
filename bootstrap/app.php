<?php

declare(strict_types=1);

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('stateless')
                ->group(base_path('routes/stateless.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state', 'vheart_cookie_consent']);

        $middleware->web(
            append: [
                App\Http\Middleware\ValidateSecFetchHeaders::class,
                HandleAppearance::class,
                HandleInertiaRequests::class,
                AddLinkHeadersForPreloadedAssets::class,
            ], prepend: [
                App\Http\Middleware\StagingGateMiddleware::class,
                App\Http\Middleware\Localization::class,
            ], remove: [
                ValidateCsrfToken::class,
            ]);

        $middleware->appendToPriorityList(
            StartSession::class,
            App\Http\Middleware\StagingGateMiddleware::class,
        );

        $middleware->group('stateless', [
            Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->trustProxies('*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Sentry\Laravel\Integration::handles($exceptions);
    })->create();
