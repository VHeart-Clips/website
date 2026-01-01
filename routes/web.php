<?php

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

    Route::get('/submit-clip', function (Request $request) {
        $clipUrl = (string)$request->query('clip_url', '');
        $preview = null;

        if ($clipUrl !== '') {
            $clipId = null;

            if (preg_match('~clips\.twitch\.tv/([A-Za-z0-9_-]+)~i', $clipUrl, $m)) {
                $clipId = $m[1];
            } elseif (preg_match('~twitch\.tv/[^/]+/clip/([A-Za-z0-9_-]+)~i', $clipUrl, $m)) {
                $clipId = $m[1];
            }

            if ($clipId) {
                $parent = $request->getHost();

                $preview = [
                    'ok' => true,
                    'can_submit' => true,
                    'clip' => [
                        'clip_id' => $clipId,
                        'embed_url' => "https://clips.twitch.tv/embed?clip={$clipId}&parent={$parent}",
                        'broadcaster_login' => null,
                        'game_name' => null,
                    ],
                ];
            } else {
                $preview = [
                    'ok' => false,
                    'can_submit' => false,
                    'errors' => [__('sendinclip.errors.invalid_clip_url')],
                ];
            }
        }
        // TODO: Wo DB daten? :c
        return Inertia::render('submitclip', [
            'preview' => $preview,
            'tags' => [
                ['id' => 1, 'name' => 'Funny'],
                ['id' => 2, 'name' => 'Fail'],
                ['id' => 3, 'name' => 'Clutch'],
            ],
        ]);
    })->name('submitclip');

    Route::post('/clips', function (Request $request) {
        $data = $request->validate([
            'clip_url' => ['required', 'string'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer'],
            'is_anonymous' => ['sometimes', 'boolean'],
        ]);

        $clipUrl = $data['clip_url'];
        $tagIds = $data['tag_ids'] ?? [];
        $isAnonymous = (bool) ($data['is_anonymous'] ?? false);

        $senderUserId = Auth::id();

        // TODO: Backend-Checks + Speichern in DB
        // - broadcaster registriert
        // - consent vorhanden
        // - user blocklist
        // - games allow/deny
        // - user roles erlaubt
        // etc.

        return redirect()
            ->route('submitclip')
            ->with('submit_ok', true)
            ->with('submit_message', __('sendinclip.flash.submitted'));
    })->middleware(['auth', 'verified'])->name('clips.store');

    Route::get('/team', TeamController::class)->name('team');

    Route::get('/about-us', function () {
        return Inertia::render('about');
    })->name('about');
});

Route::get('/auth/twitch', function() {
    return Socialite::driver('twitch')->scopes(['channel:read:vips', 'user:read:moderated_channels'])->redirect();
})->name('auth.twitch');

Route::get('/auth/twitch/callback', function() {
    try {
        $twitchUser = Socialite::driver('twitch')->user();
    } catch (\Exception $e) {
        return to_route('login')->with('error', __('auth.oauth_error_try_again'));
    }

    $user = User::updateOrCreate([
        'id' => $twitchUser->getId()
    ],
    [
        'name' => $twitchUser->getName(),
        'avatar_url' => $twitchUser->getAvatar(),
        'twitch_refresh_token' => $twitchUser->refreshToken,
    ]);

    session()?->regenerate();
    Auth::login($user);
    session()->put('twitch_access_token', $twitchUser->token);

    return to_route('dashboard');
})->name('auth.callback');

Route::get('/locales.json', \App\Actions\Locales::class)->name('locales');

Route::get('/locales/{lang}', static function (Request $request, $lang) {
    if (!array_key_exists($lang, Config::get('app.locales'))) {
        abort(422); // we understand it but its invalid
    }

    app()->setLocale($lang);
    session()?->put('locale', $lang);

    return new JsonResponse([
        'message' => __('Ok!')
    ], 200);
});

require __DIR__ . '/settings.php';
