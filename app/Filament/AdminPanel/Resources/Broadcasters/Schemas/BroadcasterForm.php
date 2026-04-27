<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters\Schemas;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Clips\ClipStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\User;
use App\Services\Twitch\Data\ChannelDto;
use App\Services\Twitch\Data\UserDto;
use App\Services\Twitch\TwitchService;
use App\Support\Audit\Auditor;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class BroadcasterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::makeUserSection(),
            self::makeConsentSection(),
            self::makeSubmitPermissionsSection(),
        ]);
    }

    private static function makeConsentSection(): Section
    {
        return Section::make('Consent')
            ->hiddenOn(Operation::Create)
            ->schema([
                CheckboxList::make('consent')
                    ->options(BroadcasterConsent::class)
                    ->gridDirection('row')
                    ->label('Given Consents')
                    ->columns(2),

                Select::make('default_clip_status')
                    ->options(
                        collect(ClipStatus::defaultableOptions())
                            ->prepend(ClipStatus::Unknown)
                            ->mapWithKeys(fn (ClipStatus $status): array => [$status->value => $status->getLabel()])
                            ->toArray()
                    ),

                CheckboxList::make('twitch_mod_permissions')
                    ->options(BroadcasterPermission::class)
                    ->gridDirection('row')
                    ->label('Mod Permissions')
                    ->columns(2)
                    // hidden for now since we did not implement that currently
                    ->hidden(),
            ]);
    }

    private static function makeSubmitPermissionsSection(): Section
    {
        return Section::make('Submission Permissions')
            ->hiddenOn(Operation::Create)
            ->schema([
                Toggle::make('submit_user_allowed')
                    ->afterStateUpdated(function (bool $state, Set $set): void {
                        if ($state) {
                            $set('submit_vip_allowed', true);
                            $set('submit_mods_allowed', true);
                        }
                    })
                    ->onIcon(LucideIcon::Check)
                    ->offIcon(LucideIcon::X)
                    ->onColor('success')
                    ->label('Everyone')
                    ->live(),

                Toggle::make('submit_vip_allowed')
                    ->afterStateUpdated(function (bool $state, Set $set): void {
                        if (! $state) {
                            $set('submit_user_allowed', false);
                        }
                    })
                    ->onIcon(LucideIcon::Check)
                    ->offIcon(LucideIcon::X)
                    ->onColor('success')
                    ->label('VIPs')
                    ->live(),

                Toggle::make('submit_mods_allowed')
                    ->afterStateUpdated(function (bool $state, Set $set): void {
                        if (! $state) {
                            $set('submit_user_allowed', false);
                        }
                    })
                    ->onIcon(LucideIcon::Check)
                    ->offIcon(LucideIcon::X)
                    ->onColor('success')
                    ->label('Mods')
                    ->live(),
            ]);
    }

    private static function makeUserSection(): Select
    {
        return Select::make('id')
            ->visibleOn(Operation::Create)
            ->columnSpanFull()
            ->relationship(
                name: 'user',
                titleAttribute: 'name',
                modifyQueryUsing: fn (Builder $query) => $query->whereDoesntHave('broadcaster'),
            )
            ->label('User')
            ->searchable()
            ->createOptionAction(
                fn (Action $action): Action => $action
                    ->authorize('importUser')
                    ->modalDescription('Only import users who have previously interacted with the platform or given consent. Importing arbitrary Twitch users may violate GDPR or Twitch TOS.')
            )
            ->createOptionModalHeading('Import User')
            ->createOptionForm([
                Select::make('twitch_user_id')
                    ->label('Search Users on Twitch')
                    ->required()
                    ->searchable()
                    ->live()
                    ->getSearchResultsUsing(function (string $search, TwitchService $twitchService): array {
                        $channels = collect($twitchService->asSessionUser()->searchChannels($search, 100));

                        $existingIds = User::whereHas('broadcaster')
                            ->whereIn('id', $channels->pluck('id'))
                            ->pluck('id')
                            ->map(fn ($id): string => (string) $id)
                            ->all();

                        return $channels
                            ->reject(fn (ChannelDto $c): bool => in_array((string) $c->id, $existingIds, true))
                            ->sortBy(fn (ChannelDto $c): int => levenshtein(mb_strtolower($search), mb_strtolower($c->displayName)))
                            ->take(10)
                            ->mapWithKeys(fn (ChannelDto $c): array => [$c->id => $c->displayName])
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn (?string $value, TwitchService $twitchService): ?string => User::find($value)?->name ?? self::getUser($value, $twitchService)?->displayName)
                    ->afterStateUpdated(function (?string $state, Set $set, TwitchService $twitchService): void {
                        if (! $state) {
                            return;
                        }

                        $user = self::getUser($state, $twitchService);

                        if (! $user instanceof UserDto) {
                            $set('twitch_name', null);
                            $set('twitch_avatar', null);
                            $set('twitch_id', null);

                            return;
                        }

                        $set('twitch_name', $user->displayName);
                        $set('twitch_avatar', $user->profileImageUrl);
                        $set('twitch_id', $state);
                    }),
                Checkbox::make('i_have_consent')
                    ->label('I received consent from the user to import them')
                    ->accepted(),

                Hidden::make('twitch_name'),
                Hidden::make('twitch_avatar'),
                Hidden::make('twitch_id'),
            ])
            ->createOptionUsing(function (array $data): string {
                $user = User::firstOrCreate(
                    ['id' => $data['twitch_id']],
                    [
                        'name' => $data['twitch_name'],
                        'avatar_url' => $data['twitch_avatar'],
                        'id' => $data['twitch_id'],
                    ]
                );

                Auditor::make()
                    ->event($user->wasRecentlyCreated ? 'created' : 'updated')
                    ->on($user)
                    ->save();

                return (string) $user->getKey();
            })
            ->preload();
    }

    private static function getUser(int|string $id, TwitchService $twitchService): ?UserDto
    {
        return Cache::remember(
            self::class.":twitch_user_search:{$id}",
            now()->addMinute(),
            static fn (): ?\App\Services\Twitch\Data\UserDto => array_first($twitchService->asSessionUser()->getUsers(['id' => $id]))
        );
    }
}
