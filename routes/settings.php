<?php

declare(strict_types=1);

use App\Enums\FeatureFlag;
use App\Http\Controllers\Auth\User\DeleteUserController;
use App\Http\Controllers\Settings\DataExportController;
use App\Http\Controllers\Settings\UpdatePreferencesController;
use App\Http\Middleware\FeatureFlagGuard;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', FeatureFlagGuard::of(FeatureFlag::UserSettings)])
    ->prefix('settings')
    ->name('user.settings')
    ->group(function () {
        Route::get('/', function (Request $request, AppAuthentication $mfa) {
            return view('settings', [
                'useTwoFactor' => $mfa->isEnabled($request->user()),
            ]);
        });

        Route::post('/export', DataExportController::class)
            ->middleware(['throttle:data-export'])
            ->name('.data-export');

        Route::put('/', UpdatePreferencesController::class)->name('.preferences.update');
        Route::delete('/', DeleteUserController::class)->name('.delete');
    });
