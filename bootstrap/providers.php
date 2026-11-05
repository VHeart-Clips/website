<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\CookiesServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\DashboardPanelProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\Support\FeatureFlagServiceProvider;
use App\Providers\Support\FilamentIconServiceProvider;
use App\Providers\TwitchServiceProvider;
use SocialiteProviders\Manager\ServiceProvider as SocialiteServiceProvider;

return [
    AppServiceProvider::class,
    CookiesServiceProvider::class,
    FilamentIconServiceProvider::class,
    AdminPanelProvider::class,
    DashboardPanelProvider::class,
    HorizonServiceProvider::class,
    FeatureFlagServiceProvider::class,
    SocialiteServiceProvider::class,
    TwitchServiceProvider::class,
];
