<?php

declare(strict_types=1);

use App\Enums\FeatureFlag;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ChangeLanguageController;
use App\Http\Controllers\ClipSubmitController;
use App\Http\Controllers\ClipVoteController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\Legal\ImprintController;
use App\Http\Controllers\Legal\PrivacyController;
use App\Http\Controllers\Legal\TermsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexController::class)->name('home');
Route::get('privacy', PrivacyController::class)->name('privacy');
Route::get('imprint', ImprintController::class)->name('imprint');
Route::get('terms', TermsController::class)->name('terms');
Route::get('faq', FaqController::class)->name('faq');
Route::get('team', TeamController::class)->name('team');
Route::get('about-us', AboutUsController::class)->name('about');
Route::get('locales', ChangeLanguageController::class)->name('locales');

Route::get('leaderboard', function () {

    $ranges = [
        [
            'name' => __('leaderboard.range.this_week'),
            'start' => now()->previous(Carbon::THURSDAY),
            'end' => now()->next(Carbon::THURSDAY),
        ],
        [
            'name' => __('leaderboard.range.last_week'),
            'start' => now()->previous(Carbon::THURSDAY),
            'end' => now()->next(Carbon::THURSDAY),
        ],
        [
            'name' => __('leaderboard.range.this_month'),
            'start' => now()->previous(Carbon::THURSDAY),
            'end' => now()->next(Carbon::THURSDAY),
        ],
        [
            'name' => __('leaderboard.range.last_month'),
            'start' => now()->previous(Carbon::THURSDAY),
            'end' => now()->next(Carbon::THURSDAY),
        ],
        [
            'name' => __('leaderboard.range.this_year'),
            'start' => now()->previous(Carbon::THURSDAY),
            'end' => now()->next(Carbon::THURSDAY),
        ],
    ];

    $start = now()->previous(Carbon::THURSDAY);
    $end = now()->next(Carbon::THURSDAY);

    $topSubmitters = Cache::remember(
        'leaderboard.top.submitter',
        now()->addHour(),
        fn () => [
            'timestamp' => now(),
            'users' => User::query()
                ->where('id', '!=', 0)
                ->withCount([
                    'submittedClips' => fn (Builder $q) => $q->whereBetween('created_at', [$start, $end]),
                ])
                ->withMax([
                    'submittedClips' => fn (Builder $q) => $q->whereBetween('created_at', [$start, $end]),
                ], 'created_at')
                ->orderBy('submitted_clips_count', 'desc')
                ->orderBy('submitted_clips_max_created_at', 'asc')
                ->whereHas('submittedClips', fn (Builder $q): Builder => $q->whereBetween('created_at', [$start, $end]))
                ->limit(10)
                ->get()]
    );

    $topVoters = Cache::remember(
        'leaderboard.top.voter',
        now()->addHour(),
        fn () => [
            'timestamp' => now(),
            'users' => User::query()
                ->where('id', '!=', 0)
                ->withCount([
                    'votes' => fn (Builder $q) => $q->whereBetween('created_at', [$start, $end]),
                ])
                ->withMax([
                    'votes' => fn (Builder $q) => $q->whereBetween('created_at', [$start, $end]),
                ], 'created_at')
                ->orderBy('votes_count', 'desc')
                ->orderBy('votes_max_created_at', 'asc')
                ->whereHas('votes', fn (Builder $q): Builder => $q->whereBetween('created_at', [$start, $end]))
                ->limit(10)
                ->get(),
        ]);

    return view('leaderboard', [
        'ranges' => $ranges,
        'topSubmitters' => $topSubmitters,
        'topVoters' => $topVoters,
    ]);
})->name('leaderboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::feature(FeatureFlag::ClipSubmission)->group(function () {
        Route::get('/submit', [ClipSubmitController::class, 'create'])->name('submitclip.create');
        Route::post('/submit', [ClipSubmitController::class, 'store'])->name('submitclip.store');
    });

    Route::feature(FeatureFlag::ClipVoting)->group(function () {
        Route::get('/vote', [ClipVoteController::class, 'create'])->name('vote');
        Route::post('/vote', [ClipVoteController::class, 'store'])->name('vote.submit');
    });

    Route::feature(FeatureFlag::Reports)->group(function () {
        Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    });

    Route::get('dashboard', static fn (Request $request) => redirect('dashboard/'.$request->user()->id));
});

require __DIR__.'/settings.php';
require __DIR__.'/statistics.php';
require __DIR__.'/auth.php';
