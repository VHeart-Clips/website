<?php

declare(strict_types=1);

use App\Http\Controllers\LocalesController;

Route::get('/locales.json', LocalesController::class)
    ->middleware(['throttle:locales', 'cache.headers:public;max_age=3600;s_maxage=3600;stale_while_revalidate=86400;etag'])
    ->name('locales');
