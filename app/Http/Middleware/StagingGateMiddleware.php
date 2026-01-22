<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class StagingGateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cookiePrefix = 'vheart_staging';
        $cookieSession = $cookiePrefix.'_session';
        $cookieIntended = $cookiePrefix.'_intended';

        $whitelist = config('app.staging-whitelist', []);
        $currentUser = $request->cookie($cookieSession, false);

        if (! app()->environment('staging')) {
            return $next($request);
        }

        if ($currentUser) {
            [$twitchId, $twitchName] = explode(':', $currentUser);
            if (! in_array($twitchId, $whitelist, true) && ! in_array($twitchName, $whitelist, true)) {
                abort(403);
            }

            return $next($request);
        }

        if ($request->is('auth/twitch/callback')) {
            try {
                $twitchUser = Socialite::driver('twitch')->stateless()->user();
                debug($twitchUser);
            } catch (Exception) {
                return Socialite::driver('twitch')->redirect();
            }

            $intendedUrl = $request->cookie($cookieIntended, route('home'));

            return redirect()->intended($intendedUrl)->withCookies([
                Cookie::make($cookieSession, $twitchUser->id.':'.$twitchUser->user['login'], 60 * 24),
                Cookie::forget($cookieIntended),
            ]);
        }

        // simple QOL of remembering where we are to redirect back to it later
        $intendedCookie = Cookie::make($cookieIntended, $request->fullUrl(), 10);

        return Socialite::driver('twitch')->redirect()->withCookie($intendedCookie);
    }
}
