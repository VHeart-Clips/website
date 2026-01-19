<?php

declare(strict_types=1);

namespace App\Enums\Traits;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

trait HasHeadlineLabel
{
    /**
     * Returns a headline formatted label based on the case name
     *
     *  Example for `MyEnumCase`: `My Enum Case`
     */
    public function getLabel(): string|Htmlable|null
    {
        return Str::headline($this->name);
    }
}
