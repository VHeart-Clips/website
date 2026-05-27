<?php

declare(strict_types=1);

namespace App\Filament\Tables;

use App\Models\Contracts\HasFilamentTableColumn;
use Filament\Tables\Columns\Column;

/**
 * A smart column for morphable relationships.
 *
 * Resolves the column definition from the related model if it implements {@see HasFilamentTableColumn}.
 * Falls back to `Model #ID` if no resolver is provided or the interface is not implemented.
 *
 * Note: this will only work with decorative features of filament, things like `->searchable()` won't work on this or the resolved columns.
 */
class MorphColumn extends Column
{
    protected string $view = 'filament.tables.columns.morph-column';

    protected array $resolvers = [];

    public function for(string $class, Column ...$columns): static
    {
        $this->resolvers[$class] = $columns;

        return $this;
    }

    public function getResolvedColumns(): array
    {
        $related = $this->getRelated();

        if (! $related) {
            return [];
        }

        if (isset($this->resolvers[$related::class])) {
            return $this->resolvers[$related::class];
        }

        if ($related instanceof HasFilamentTableColumn) {
            return [$related::getFilamentTableColumn($this->getName())];
        }

        return [];
    }

    public function getRelated(): mixed
    {
        return $this->getRecord()?->{$this->getName()};
    }
}
