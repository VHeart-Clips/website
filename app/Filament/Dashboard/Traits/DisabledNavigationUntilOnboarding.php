<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Traits;

use App\Enums\Filament\LucideIcon;
use App\Models\Broadcaster\Broadcaster;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;

/**
 * Disables the navigation item for this page if the user is on their own channel and has not onboarded yet
 */
trait DisabledNavigationUntilOnboarding
{
    public static function getNavigationItems(): array
    {
        /** @var Broadcaster $tenant */
        $tenant = Filament::getTenant();
        $user = auth()->user();
        $items = parent::getNavigationItems();

        if ($user->id !== $tenant->id || $tenant->onboarded_at !== null) {
            return $items;
        }

        return collect($items)
            ->map(fn (NavigationItem $item): NavigationItem => $item
                ->extraAttributes([
                    'class' => 'opacity-40 cursor-not-allowed select-none [&_a]:pointer-events-none',
                    'aria-label' => __('dashboard/navigation.locked'),
                    'title' => __('dashboard/navigation.locked'),
                    'aria-disabled' => 'true',
                ])
                ->icon(LucideIcon::Lock),
            )
            ->all();
    }
}
