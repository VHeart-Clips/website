<?php

declare(strict_types=1);

use App\Http\Controllers\Locales;

Route::get('/locales.json', Locales::class)
    ->middleware(['throttle:locales'])
    ->name('locales');
