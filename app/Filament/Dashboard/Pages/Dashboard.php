<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Pages;

use App\Enums\Filament\LucideIcon;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = LucideIcon::House;

    public static function getNavigationLabel(): string
    {
        return __('dashboard/navigation.dashboard');
    }

    public function getTitle(): string|Htmlable
    {
        return Filament::getTenant()->name.' - '.self::getNavigationLabel();
    }

    public function getHeading(): string|Htmlable|null
    {
        return self::getNavigationLabel();
    }
}
