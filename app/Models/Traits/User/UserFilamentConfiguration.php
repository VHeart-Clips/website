<?php

declare(strict_types=1);

namespace App\Models\Traits\User;

use App\Enums\FeatureFlag;
use App\Enums\Filament\LucideIcon;
use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use App\Services\Twitch\TwitchService;
use App\Support\FeatureFlag\Feature;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Panel;
use Filament\Schemas\Components\Component as FilamentSchemaComponent;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Column as FilamentTableColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Component as FilamentTableComponent;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @mixin User
 */
trait UserFilamentConfiguration
{
    public static function getFilamentTableColumn(string $name): FilamentTableComponent|FilamentTableColumn
    {
        return Split::make([
            ImageColumn::make("$name.avatar")
                ->getStateUsing(fn (Model $record) => $record->$name?->proxiedContentUrl())
                ->imageHeight(30)
                ->circular(),
            Split::make([
                TextColumn::make("{$name}.name"),
            ]),
        ]);
    }

    public static function getFilamentInfolistEntry(string $name): FilamentSchemaComponent
    {
        return Grid::make()
            ->schema([
                ImageEntry::make("$name.avatar")
                    ->getStateUsing(fn (Model $record) => $record->$name?->proxiedContentUrl())
                    ->columnSpan(1)
                    ->hiddenLabel()
                    ->grow(false)
                    ->circular(),

                Grid::make(1)
                    ->schema([
                        TextEntry::make("{$name}.name")
                            ->hiddenLabel()
                            ->weight('bold')
                            ->size(TextSize::Large)
                            ->icon(LucideIcon::User),

                        TextEntry::make("{$name}.created_at")
                            ->icon(LucideIcon::Calendar)
                            ->since(),
                    ])
                    ->columnSpan(1)
                    ->grow(),
            ]);
    }

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
