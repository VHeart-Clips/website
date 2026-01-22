<?php

declare(strict_types=1);

use App\Http\Controllers\ClipSubmitController;
use App\Http\Controllers\Locales;
use App\Http\Controllers\TeamController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $settings = [
        'donationUrl' => 'https://www.betterplace.org/de/fundraising-events/55712-vheart-fuerdiesuessmaeuse',
        'partnerIcon' => null,
        'youtubeUrl' => 'https://www.youtube-nocookie.com/embed/videoseries?list=UUUefW5IjMaQS_ZFaG4VZi9A',
    ];

    return Inertia::render('welcome', $settings);
})->name('home');

Route::get('/imprint', function () {
    $locale = app()->getLocale();
    $view = "legal.$locale.imprint";

    if (! view()->exists($view)) {
        abort(404);
    }

    return view($view);
});

Route::get('/privacy', function () {
    $locale = app()->getLocale();
    $view = "legal.$locale.privacy";

    if (! view()->exists($view)) {
        abort(404);
    }

    return view($view);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/start', function () {
        return Inertia::render('start');
    })->name('start');

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
        $settings = [
            'donationUrl' => 'https://www.betterplace.org/de/fundraising-events/55712-vheart-fuerdiesuessmaeuse',
            'partnerIcon' => null,
        ];

        return Inertia::render('about', $settings);
    })->name('about');
});

Route::get('/locales.json', Locales::class)->name('locales');

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
require __DIR__.'/auth.php';
