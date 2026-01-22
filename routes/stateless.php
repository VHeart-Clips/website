<?php

declare(strict_types=1);

use App\Http\Controllers\Locales;

Route::get('/locales.json', Locales::class)
    ->middleware(['throttle:locales', 'cache.headers:public;max_age=3600;s_maxage=3600;stale_while_revalidate=86400;etag'])
    ->name('locales');
