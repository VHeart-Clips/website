<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Enums\FeatureFlag;
use App\Support\FeatureFlag\Feature;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Limit Clips to only those we have permissions for
 */
class ClipPermissionScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Feature::isActive(FeatureFlag::IgnoreBroadcasterConsentOnClipScope) || Filament::isServing()) {
            return;
        }

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $builder->whereBroadcasterGavePermission();
    }
}
