<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Socialite\Socialite;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

Route::get('/auth/twitch', function() {
    return Socialite::driver('twitch')->redirect();
});

Route::get('/auth/twitch/callback', function() {
    $twitchUser = Socialite::driver('twitch')->user();

    $user = User::updateOrCreate([
        'id' => $twitchUser->getId()
    ],
    [
        'name' => $twitchUser->getName()
    ]);

    //TODO: token zwischenspeichern für später weiterbenutzung
    Auth::login($user);

    return to_route('dashboard');
});

Route::get('/locales.json', \App\Actions\Locales::class)->name('locales');

require __DIR__.'/settings.php';
