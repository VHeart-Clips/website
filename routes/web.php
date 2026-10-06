<?php

declare(strict_types=1);

use App\Enums\FeatureFlag;
use App\Enums\Leaderboard\LeaderboardRange;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ChangeLanguageController;
use App\Http\Controllers\ClipSubmitController;
use App\Http\Controllers\ClipUploadPresignController;
use App\Http\Controllers\ClipVoteController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\Leaderboard;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\Legal\ImprintController;
use App\Http\Controllers\Legal\PrivacyController;
use App\Http\Controllers\Legal\TermsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamController;
use App\Models\User;
use App\Queries\Leaderboard\UserLeaderboardSubmitterQuery;
use App\Queries\Leaderboard\UserLeaderboardVoteQuery;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexController::class)->name('home');
Route::get('privacy', PrivacyController::class)->name('privacy');
Route::get('imprint', ImprintController::class)->name('imprint');
Route::get('terms', TermsController::class)->name('terms');
Route::get('faq', FaqController::class)->name('faq');
Route::get('team', TeamController::class)->name('team');
Route::get('about-us', AboutUsController::class)->name('about');
Route::get('locales', ChangeLanguageController::class)->name('locales');

Route::get('leaderboard', LeaderboardController::class)->name('leaderboard');

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

    Route::feature(FeatureFlag::ClipUpload)->group(function () {
        Route::post('/storage/clip-presign', ClipUploadPresignController::class)->name('clip.presign');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/statistics.php';
require __DIR__.'/auth.php';
