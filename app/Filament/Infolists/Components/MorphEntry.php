<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Models\Contracts\HasFilamentInfolistEntry;
use Filament\Infolists\Components\Entry;

/**
 * A smart entry for morphable relationships.
 *
 * Resolves the entry definition from the related model if it implements {@see HasFilamentInfolistEntry}.
 * Falls back to `Model #ID` if no resolver is provided or the interface is not implemented.
 */
class MorphEntry extends Entry
{
    protected string $view = 'filament.infolists.entries.morph-entry';

    protected array $resolvers = [];

    public function for(string $class, Entry ...$entries): static
    {
        $this->resolvers[$class] = $entries;

        return $this;
    }

    public function getExtraViewData(): array
    {
        $related = $this->getRelated();

        if ($related) {
            if (isset($this->resolvers[$related::class])) {
                $this->childComponents($this->resolvers[$related::class]);
            } elseif ($related instanceof HasFilamentInfolistEntry) {
                $this->childComponents([$related::getFilamentInfolistEntry($this->getName())]);
            }
        }

        return parent::getExtraViewData();
    }

    public function getRelated(): mixed
    {
        return $this->getRecord()?->{$this->getName()};
    }
}
