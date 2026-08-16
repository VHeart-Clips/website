<?php

declare(strict_types=1);

use App\Enums\FeatureFlag;
use App\Http\Controllers\ClipVoteController;
use App\Http\Controllers\Statistics\VotesController;
use App\Http\Controllers\StatisticsController;
use App\Http\Middleware\FeatureFlagGuard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', FeatureFlagGuard::of(FeatureFlag::UserStatistics)])
    ->prefix('statistics')
    ->name('user.statistics')
    ->group(function () {
        Route::get('/', StatisticsController::class);
        Route::get('/votes', VotesController::class)->name('.votes');
        Route::post('/votes', [ClipVoteController::class, 'update'])->name('.votes.update');
    });
