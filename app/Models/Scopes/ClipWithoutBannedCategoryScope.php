<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Filament\Facades\Filament;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Hide clips that have banned categories or where the category is missing
 */
class ClipWithoutBannedCategoryScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Filament::isServing()) {
            return;
        }

        $builder->where(fn (Builder $query) => $query->whereHas('category', function (Builder $builder): void {
            $builder->where('is_banned', false);
        })->orWhereDoesntHave('category', function (Builder $builder): void {
            $builder->where('is_banned', true);
        }));
    }
}
