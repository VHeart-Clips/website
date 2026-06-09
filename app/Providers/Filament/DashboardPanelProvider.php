<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\FeatureFlag;
use App\Enums\Filament\LucideIcon;
use App\Filament\Dashboard\Pages\Dashboard;
use App\Http\Middleware\FeatureFlagGuard;
use App\Http\Middleware\Localization;
use App\Http\Middleware\RequiresBroadcasterProfile;
use App\Http\Middleware\StagingGateMiddleware;
use App\Models\Ban;
use App\Models\Broadcaster\Broadcaster;
use App\Support\FeatureFlag\Feature;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Throwable;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $adminPanel = Filament::getPanel('admin');

        return $panel
            ->default()
            ->plugin(SpatieTranslatablePlugin::make()->defaultLocales(['de', 'en'])->useFallbackLocale(false))
            ->emailChangeVerification()
            ->multiFactorAuthentication([
                AppAuthentication::make()
                    ->regenerableRecoveryCodes(false)
                    ->recoverable()
                    ->brandName('VHeart'),
            ])
            ->id('dashboard')
            ->path('dashboard')
            ->viteTheme('resources/css/filament/dashboard.css')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->maxContentWidth(Width::ScreenTwoExtraLarge)
            ->darkModeBrandLogo(fn () => Vite::asset('resources/images/svg/logo-full-dark.svg'))
            ->brandLogo(fn () => Vite::asset('resources/images/svg/logo-full-title.svg'))
            ->brandLogoHeight('2rem')
            ->homeUrl('/')
            ->userMenuItems([
                Action::make('back-home')
                    ->label('dashboard/navigation.back-home')
                    ->translateLabel()
                    ->url(fn (): string => route('home'))
                    ->icon(LucideIcon::Home)
                    ->sort(100),
                Action::make('to-admin')
                    ->label('navigation.team_dashboard')
                    ->translateLabel()
                    ->hidden(fn (): bool => ! auth()->user()->canAccessPanel($adminPanel))
                    ->url(fn (): string => $adminPanel->getUrl())
                    ->icon(LucideIcon::LayoutDashboard)
                    ->sort(100),
            ])
            ->tenant(Broadcaster::class)
            ->searchableTenantMenu()
            ->tenantMenu(fn (): bool => Feature::isActive(FeatureFlag::BroadcasterTenant))
            ->discoverResources(in: app_path('Filament/Dashboard/Resources'), for: 'App\Filament\Dashboard\Resources')
            ->discoverPages(in: app_path('Filament/Dashboard/Pages'), for: 'App\Filament\Dashboard\Pages')
            ->discoverPages(in: app_path('Filament/Dashboard/Pages/Broadcaster'), for: 'App\Filament\Dashboard\Pages\Broadcaster')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Dashboard/Widgets'), for: 'App\Filament\Dashboard\Widgets')
            ->databaseNotifications()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                RequiresBroadcasterProfile::class,
            ])
            ->renderHook(
                PanelsRenderHook::PAGE_START,
                fn (): ?HtmlString => $this->renderBanNotice(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => '<script type="module" src="'.Vite::asset('resources/js/alpine.ts').'"></script>',
            )
            ->authMiddleware([
                Authenticate::class,
                StagingGateMiddleware::class,
                Localization::class,
                FeatureFlagGuard::of(FeatureFlag::UserDashboard),
            ])
            ->spa()
            ->spaUrlExceptions(fn (): array => [
                url('/'),
                url('/admin'),
                '*/admin*',
            ]);
    }

    /**
     * @throws Throwable
     */
    private function renderBanNotice(): ?HtmlString
    {
        /** @var Broadcaster $currentTenant */
        $currentTenant = Filament::getTenant();
        $currentBan = $currentTenant?->getBan();
        $userBan = $currentTenant->user?->getBan();

        $ban = collect([$currentBan, $userBan])
            ->filter()
            ->sortByDesc(fn (Ban $ban): int => $ban->banned_until?->timestamp ?? PHP_INT_MAX)
            ->first();

        if (! $ban) {
            return null;
        }

        return new HtmlString(view('filament.dashboard.components.alerts.ban-notice', [
            'ban' => $ban,
            'tenant' => $currentTenant,
        ])->render());
    }
}
