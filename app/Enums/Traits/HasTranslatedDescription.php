<?php

declare(strict_types=1);

namespace App\Enums\Traits;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

trait HasTranslatedDescription
{
    /**
     * Returns a translated string based on this format:
     *
     * `<prefix>.<kebab case enum class name>-description.<lowercase case name>`
     *
     * Example key with `CollectionStatus->Internal` and default prefix:
     *
     * `enums.collection-status-description.internal`
     */
    public function getDescription(): string|Htmlable|null
    {
        $enumClassName = Str::kebab(class_basename(static::class)).'-description';
        $enumValueName = Str::kebab($this->name);

        return __("{$this->getTranslatableEnumDescriptionPrefix()}.{$enumClassName}.{$enumValueName}");
    }

    /**
     * Returns the prefix of the translation path
     * e.g. "enums" will result in `enums.<enum>.<name>`
     */
    private function getTranslatableEnumDescriptionPrefix(): string
    {
        return 'enums';
    }
}
