<?php

declare(strict_types=1);

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\FeatureFlagGuard;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\Localization;
use App\Http\Middleware\StagingGateMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

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
        $middleware->preventRequestForgery(originOnly: true);

        $middleware->web(
            append: [
                HandleAppearance::class,
                AddLinkHeadersForPreloadedAssets::class,
            ], prepend: [
                StagingGateMiddleware::class,
                Localization::class,
            ]);

        $middleware->appendToPriorityList(
            StartSession::class,
            StagingGateMiddleware::class,
        );

        $middleware->prependToPriorityList(
            SubstituteBindings::class,
            FeatureFlagGuard::class
        );

        $middleware->prependToPriorityList(
            StartSession::class,
            FeatureFlagGuard::class
        );

        $middleware->group('stateless', [
            SubstituteBindings::class,
        ]);

        $middleware->prepend(AssignRequestId::class);
        $middleware->trustProxies(
            at: [
                '127.0.0.1',
                '10.0.0.0/8',
                '172.16.0.0/12',
                '192.168.0.0/16',
            ],
            headers: SymfonyRequest::HEADER_X_FORWARDED_FOR |
            SymfonyRequest::HEADER_X_FORWARDED_HOST |
            SymfonyRequest::HEADER_X_FORWARDED_PORT |
            SymfonyRequest::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;

            if (! $request->expectsJson()
                && $request->header('Referer')
                && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])
            ) {
                return back()
                    ->with('error', "Too many requests. Try again in $retryAfter seconds.")
                    ->withInput();
            }

            return response('Too many requests.', 429, $e->getHeaders());
        });
    })->create();
