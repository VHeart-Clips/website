<?php

declare(strict_types=1);

use App\Http\Middleware\StagingGateMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    app()->detectEnvironment(fn () => 'staging');
});

it('passes through on non-staging env', function () {
    app()->detectEnvironment(fn () => 'production');

    $request = Request::create('/');
    $response = (new StagingGateMiddleware)->handle($request, fn () => response('ok'));

    expect($response->getContent())->toBe('ok');
});

it('passes through when access cookie set', function () {
    $request = Request::create('/');
    $request->cookies->set('vheart_staging_access', '1');

    $response = (new StagingGateMiddleware)->handle($request, fn () => response('ok'));

    expect($response->getContent())->toBe('ok');
});

it('passes through when session cookie user has role', function () {
    DB::shouldReceive('getConnections')->andReturn([]);
    DB::shouldReceive('table')->with('user_roles')->andReturnSelf();
    DB::shouldReceive('where')->with('user_id', '42')->andReturnSelf();
    DB::shouldReceive('exists')->andReturn(true);

    $request = Request::create('/');
    $request->cookies->set('vheart_staging_session', '42');

    $response = (new StagingGateMiddleware)->handle($request, fn () => response('ok'));

    expect($response->getContent())->toBe('ok')
        ->and(Cookie::queued('vheart_staging_access')?->getValue())->toBe('1')
        ->and(Cookie::queued('vheart_staging_access')?->getExpiresTime())->toBe(now()->addHour()->timestamp)
        ->and(Cookie::queued('vheart_staging_access')?->getMaxAge())->toBe(60 * 60);
});

it('aborts 403 when session cookie user has no role', function () {
    DB::shouldReceive('getConnections')->andReturn([]);
    DB::shouldReceive('table')->with('user_roles')->andReturnSelf();
    DB::shouldReceive('where')->with('user_id', '42')->andReturnSelf();
    DB::shouldReceive('exists')->andReturn(false);

    $request = Request::create('/');
    $request->cookies->set('vheart_staging_session', '42');

    (new StagingGateMiddleware)->handle($request, fn () => response('ok'));
})->throws(HttpException::class);

it('does not query db when access cookie set', function () {
    DB::shouldReceive('getConnections')->andReturn([]);
    DB::shouldReceive('table')->never();

    $request = Request::create('/');
    $request->cookies->set('vheart_staging_access', '1');

    (new StagingGateMiddleware)->handle($request, fn () => response('ok'));
});
