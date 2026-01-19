<?php

declare(strict_types=1);

use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['guest'])->group(function () {
    Route::get('login', static function (Request $request) {
        return Inertia::render('auth/login', [
            'status' => $request->session()->get('status'),
        ]);
    })
        ->name('login');

    Route::get('/auth/twitch', static function () {
        return Socialite::driver('twitch')->scopes(['channel:read:vips', 'user:read:moderated_channels'])->redirect();
    })
        ->name('auth.twitch');

    Route::get('/auth/twitch/callback', function () {
        try {
            $twitchUser = Socialite::driver('twitch')->user();
        } catch (Exception $e) {
            return to_route('login')->with('error', __('auth.oauth_error_try_again'));
        }

        $user = User::updateOrCreate([
            'id' => $twitchUser->getId(),
        ],
            [
                'name' => $twitchUser->getName(),
                'avatar_url' => $twitchUser->getAvatar(),
                'twitch_refresh_token' => $twitchUser->refreshToken,
            ]);

        if ($user->deleted_at) {
            return to_route('login')->withErrors(['login' => __('user.disabled')]);
        }

        session()?->regenerate();
        Auth::login($user);
        session()->put('twitch_access_token', $twitchUser->token);

        return redirect()->intended(route('dashboard'));
    })
        ->middleware(['throttle:login'])
        ->name('auth.callback');
});

Route::post('logout', static function (Request $request) {
    auth()->logout();

    if ($request->hasSession()) {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return to_route('home');
})
    ->middleware(['auth:web'])
    ->name('logout');

Route::middleware(['auth:web'])
    ->prefix('email')
    ->name('verification.')
    ->group(function () {
        Route::get('verify', static function (Request $request) {
            if ($request->user()->email === null || $request->user()->hasVerifiedEmail()) {
                return redirect()->intended(route('dashboard'));
            }

            return Inertia::render('auth/verify-email');
        })
            ->name('notice');

        Route::get('verify/{id}/{hash}', static function (VerifyEmailRequest $request) {
            if ($request->user()->hasVerifiedEmail()) {
                return redirect()->intended(route('dashboard', ['verified' => true]));
            }

            if ($request->user()->markEmailAsVerified()) {
                event(new Verified($request->user()));
            }

            return redirect()->intended(route('dashboard', ['verified' => true]));
        })
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verify');

        Route::post('verification-notification', static function (Request $request) {
            if ($request->user()->hasVerifiedEmail()) {
                return $request->wantsJson()
                    ? new JsonResponse(status: 204)
                    : redirect()->intended(route('dashboard'));
            }

            $request->user()->sendEmailVerificationNotification();

            return $request->wantsJson()
                ? new JsonResponse(status: 202)
                : back()->with('status', __('auth.verification.sent'));

        })
            ->middleware(['throttle:6,1'])
            ->name('send');
    });
