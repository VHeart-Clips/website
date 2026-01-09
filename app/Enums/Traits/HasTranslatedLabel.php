<?php

declare(strict_types=1);

namespace App\Enums\Traits;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

trait HasTranslatedLabel
{
    /**
     * Returns a translated string based on this format:
     *
     * `enums.<kebab case enum class name>.<lowercase case name>`
     *
     * Example key with `CollectionStatus->Internal`:
     *
     * `enums.collection-status.internal`
     */
    public function getLabel(): string|Htmlable|null
    {
        $enumClassName = Str::kebab(class_basename(static::class));
        $enumValueName = Str::lower($this->name);

        return __("enums.{$enumClassName}.{$enumValueName}");
    }
}
