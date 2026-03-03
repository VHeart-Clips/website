<?php

declare(strict_types=1);

use App\Http\Controllers\ClipSubmitController;
use App\Http\Controllers\ClipVoteController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamController;
use App\Models\Clip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', static function () {

    $bestRated = Clip::query()
        ->where('created_at', '>', now()->subDays(30))
        ->whereHas('votes', fn ($q) => $q->where('voted', true)->where('type', App\Enums\ClipVoteType::Public))
        ->withCount(['votes' => fn ($q) => $q->where('voted', true)->where('type', App\Enums\ClipVoteType::Public)])
        ->orderByDesc('votes_count')
        ->limit(10)
        ->get();

    return Inertia::render('start', [
        'bestRated' => $bestRated->toResourceCollection(),
        'discover' => Inertia::scroll(static function () {
            $discover = Clip::query()
                ->withCount(['votes' => fn ($q) => $q->where('voted', true)->where('type', App\Enums\ClipVoteType::Public)])
                ->orderByDesc('created_at')
                ->cursorPaginate();

            return $discover->toResourceCollection();
        }),
    ]);
})
    ->name('home');

Route::get('/static', static function () {

    $bestRated = Clip::query()
        ->where('created_at', '>', now()->subDays(30))
        ->whereHas('votes', fn ($q) => $q->where('voted', true)->where('type', App\Enums\ClipVoteType::Public))
        ->withCount(['votes' => fn ($q) => $q->where('voted', true)->where('type', App\Enums\ClipVoteType::Public)])
        ->orderByDesc('votes_count')
        ->limit(10)
        ->get();

    $discover = Clip::query()
        ->withCount(['votes' => fn ($q) => $q->where('voted', true)->where('type', App\Enums\ClipVoteType::Public)])
        ->orderByDesc('created_at')
        ->cursorPaginate();

    return view('index', [
        'bestRated' => $bestRated,
        'discover' => $discover,
    ]);
})
    ->name('static');

Route::get('/about-us', static function () {
    $settings = [
        'donationUrl' => 'https://www.betterplace.org/de/fundraising-events/55712-vheart-fuerdiesuessmaeuse',
        'partnerIcon' => null,
        'youtubeUrl' => 'https://www.youtube-nocookie.com/embed/videoseries?list=UUUefW5IjMaQS_ZFaG4VZi9A',
    ];

    return Inertia::render('welcome', $settings);
})->name('home');

Route::get('/imprint', function () {
    $locale = app()->getLocale();

    return view('legal', ['locale' => $locale, 'type' => 'imprint']);
});

Route::get('/privacy', function () {
    $locale = app()->getLocale();

    return view('legal', ['locale' => $locale, 'type' => 'privacy']);
});

Route::get('/faq', [FaqController::class, 'index'])->name('faq');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/submit', [ClipSubmitController::class, 'create'])->name('submitclip.create');

    Route::post('/submit', [ClipSubmitController::class, 'store'])->name('submitclip.store');

    Route::get('/vote', [ClipVoteController::class, 'create'])->name('vote');

    Route::post('/vote', [ClipVoteController::class, 'store'])->middleware('throttle:10,1')->name('vote.submit');

    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});

Route::get('/team', TeamController::class)->name('team');

Route::get('/about-us', function () {
    $settings = [
        'donationUrl' => 'https://www.betterplace.org/de/fundraising-events/55712-vheart-fuerdiesuessmaeuse',
        'partnerIcon' => null,
    ];

    return Inertia::render('about', $settings);
})->name('about');

Route::get('/locales', static function (Request $request) {
    $lang = $request->input('locale', 'en');

    if (! array_key_exists($lang, Config::get('app.locales'))) {
        if (! $request->expectsJson()) {
            return redirect()->back()->withErrors([
                'locale' => 'Invalid locale selected',
            ]);
        }

        abort(422);
    }

    app()->setLocale($lang);
    session()?->put('locale', $lang);

    if ($request->expectsJson()) {
        return new JsonResponse([
            'message' => __('Ok!'),
        ], 200);
    }

    return redirect()->back();
})->name('locales');

require __DIR__.'/dashboard.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
