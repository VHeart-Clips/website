<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\Clip;
use App\Models\Game;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(static function (SocialiteWasCalled $event) {
            $event->extendSocialite('twitch', TwitchProvider::class);
        });

        $this->configureGates();
        $this->configureVite();
        $this->configureModels();
        $this->configureCarbon();
        $this->configureEloquent();
        $this->configureOther();
    }

    private function configureGates(): void
    {
        // Check if $user has $ability in any of their roles
        Gate::before(static function (User $user, $ability) {
            $requestedPermission = $ability instanceof Permission
                ? $ability
                : Permission::tryFrom($ability);

            if (!$requestedPermission) {
                return null;
            }

            return in_array($requestedPermission, $user->permissions());
        });
    }

    private function configureVite(): void
    {
        Vite::useAggressivePrefetching();
    }

    private function configureModels(): void
    {
        // Tell laravel to automatically resolve relationship loading for us, improves performance and saves time
        Model::automaticallyEagerLoadRelationships();

        // Since we make sure to never put user request without validation anywhere near the model, we can use this to save time
        Model::unguard();

        // General Safeguards against mistakes that can cause errors or performance hits
        Model::shouldBeStrict();
    }

    private function configureCarbon(): void
    {
        // Prevents accidental mutation of dates when carbon is used
        Date::use(CarbonImmutable::class);
    }

    private function configureEloquent(): void
    {
        // Disable these commands in production environment
        DB::prohibitDestructiveCommands(app()->isProduction());

        Relation::enforceMorphMap([
            'user' => User::class,
            'clip' => Clip::class,
            'role' => Role::class,
            'game' => Game::class,
        ]);

        // Some logging for us so we can see if there are issues
        DB::whenQueryingForLongerThan(config('database.warn-threshold.slow-queries'),
            static function (Connection $connection) {
                Log::channel(config('logging.slow-queries-channel'))->warning("Database queries exceeded 5 seconds on {$connection->getName()}");
            });

        DB::listen(static function ($query) {
            if ($query->time > config('database.warn-threshold.slow-query')) {
                Log::channel(config('logging.slow-queries-channel'))->warning("An individual database query exceeded 350 milliseconds.",
                    [
                        'sql' => $query->sql,
                        'time_ms' => $query->time,
                    ]);
            }
        });
    }

    private function configureOther(): void
    {
        if (app()->isProduction() || str_starts_with(config('app.url'), 'https://')) {
            URL::forceHttps();
        }

        Inertia::encryptHistory();
    }
}
