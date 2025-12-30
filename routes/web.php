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

    //todo: hier muessen wir die daten von der DB laden
    Route::get('/team', function () {
        return Inertia::render('team', [
            'roles' => [
                [
                    'name' => 'Admin',
                    'members' => [
                        ['name' => 'DasOnkeelchen', 'avatar' => ''],
                        ['name' => 'Pandi', 'avatar' => ''],
                        ['name' => 'Meyn', 'avatar' => ''],
                        ['name' => 'Yura', 'avatar' => ''],
                    ],
                ],
                [
                    'name' => 'Community Manager',
                    'members' => [
                        ['name' => 'Yura', 'avatar' => ''],
                    ],
                ],
                [
                    'name' => 'Mod',
                    'members' => [
                        ['name' => 'SirChaos_1337', '' => ''],
                        ['name' => 'EinfachTamTam', '' => ''],
                    ],
                ],
                [
                    'name' => 'Jr Mod',
                    'members' => [
                        ['name' => 'DragonSebiii', '' => ''],
                        ['name' => 'Kayaba_sama', '' => ''],
                        ['name' => '𝔎𝔞𝔴𝔞𝔦𝔦𝔇𝔢𝔰𝔲𝔫𝔢', '' => ''],
                        ['name' => 'BastianToGo', '' => ''],
                        ['name' => 'SakuYue', '' => ''],
                        ['name' => 'Xayrie', '' => ''],
                    ],
                ],
                [
                    'name' => 'Cutter',
                    'members' => [
                        ['name' => 'DasOnkeelchen', '' => ''],
                        ['name' => 'Pandi', 'avatar' => ''],
                        ['name' => 'Meyn', 'avatar' => ''],
                        ['name' => 'Yura', 'avatar' => ''],
                    ],
                ],
                [
                    'name' => 'IT-Management',
                    'members' => [
                        ['name' => 'Katty_Terra', 'avatar' => ''],
                    ],
                ],
                [
                    'name' => 'Developer/Technician',
                    'members' => [
                        ['name' => 'JustPlayer', 'avatar' => ''],
                        ['name' => 'Speidy674', 'avatar' => ''],
                        ['name' => 'JaxOff', 'avatar' => ''],
                        ['name' => 'Tawi', 'avatar' => ''],
                    ],
                ],
            ],
        ]);
    })->name('team');

    Route::get('/about-us', function () {
        return Inertia::render('about');
    })->name('about');
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
        'name' => $twitchUser->getName(),
        'avatar_url' => $twitchUser->getAvatar()
    ]);

    //TODO: token zwischenspeichern für später weiterbenutzung
    Auth::login($user);

    return to_route('dashboard');
});

Route::get('/locales.json', \App\Actions\Locales::class)->name('locales');

require __DIR__.'/settings.php';
