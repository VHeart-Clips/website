<?php

declare(strict_types=1);

namespace App\Models\Traits\User;

use App\Enums\FeatureFlag;
use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use App\Services\Twitch\TwitchService;
use App\Support\FeatureFlag\Feature;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @mixin User
 */
trait UserFilamentConfiguration
{
    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->proxiedContentUrl();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'dashboard') {
            return true;
        }

        return $this->canAny([
            Permission::ViewAnyFaqEntry,
            Permission::ViewAnyClip,
            Permission::ViewAnyRole,
            Permission::ViewAnyUser,
            Permission::ViewAnyCategory,
            Permission::ViewAnyReport,
            Permission::ViewAnyCompilation,
        ]);
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($tenant->id === $this->id) {
            return true;
        }

        if (! Feature::isActive(FeatureFlag::BroadcasterTenant)) {
            return false;
        }

        if ($this->broadcasterTeamMembers()->pluck('broadcaster_id')->contains($tenant->id)) {
            return true;
        }

        $twitchService = app(TwitchService::class);

        // TODO: check if any twitch permission is set on broadcaster to allow twitch mods access

        return $twitchService
            ->asSessionUser()
            ->isModeratorFor($tenant->user);
    }

    /**
     * @return array<Model> | Collection
     */
    public function getTenants(Panel $panel): array|Collection
    {
        if ($panel->getId() !== 'dashboard' || ! Feature::isActive(FeatureFlag::BroadcasterTenant)) {
            return [];
        }

        $broadcasterIds = $this->broadcasterTeamMembers()->pluck('broadcaster_id');
        if ($this->broadcaster) {
            $broadcasterIds->add($this->broadcaster->id);
        }

        $twitchService = app(TwitchService::class);
        $broadcasterIds->push($twitchService->asSessionUser()->getModeratedChannels());

        // TODO: check if any twitch permission is set on broadcaster to allow twitch mods access

        return Broadcaster::findMany($broadcasterIds->unique());
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->broadcaster;
    }
}
