<?php

declare(strict_types=1);
use App\Http\Controllers\RedirectController;

Route::domain('go.vheart.net')
    ->group(function () {
        Route::get('{slug?}', RedirectController::class)
            ->name('shorturl.redirect')
            ->where('slug', '.*');
    });
