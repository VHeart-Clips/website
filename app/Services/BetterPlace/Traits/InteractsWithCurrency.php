<?php

declare(strict_types=1);

namespace App\Services\BetterPlace\Traits;

use Illuminate\Support\Number;

trait InteractsWithCurrency
{
    private function toCurrencyString(int $number): string
    {
        return Number::currency(
            number: $number / 100,
            in: 'EUR',
            locale: app()->currentLocale(),
            precision: 2);
    }
}
