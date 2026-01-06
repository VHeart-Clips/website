<?php

use App\Http\Controllers\ClipSubmitController;
use App\Http\Controllers\TeamController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Socialite\Socialite;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('/submit', [ClipSubmitController::class, 'create'])->name('submitclip.create');

    Route::post('/submit', [ClipSubmitController::class, 'store'])->name('submitclip.store');

    Route::get('/evaluateclips', function () {
        return Inertia::render('evaluateclips');
    })->name('evaluateclips');

    Route::get('/team', TeamController::class)->name('team');

    Route::get('/about-us', function () {
        return Inertia::render('about');
    })->name('about');
});

Route::get('/auth/twitch', function () {
    return Socialite::driver('twitch')->scopes(['channel:read:vips', 'user:read:moderated_channels'])->redirect();
})->name('auth.twitch');

Route::get('/auth/twitch/callback', function () {
    try {
        $twitchUser = Socialite::driver('twitch')->user();
    } catch (\Exception $e) {
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

    return to_route('dashboard');
})->name('auth.callback');

Route::get('/locales.json', \App\Actions\Locales::class)->name('locales');

Route::get('/locales/{lang}', static function (Request $request, $lang) {
    if (! array_key_exists($lang, Config::get('app.locales'))) {
        abort(422); // we understand it but its invalid
    }

    app()->setLocale($lang);
    session()?->put('locale', $lang);

    return new JsonResponse([
        'message' => __('Ok!'),
    ], 200);
});

require __DIR__.'/settings.php';
