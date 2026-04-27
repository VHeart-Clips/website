<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters\Schemas;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Broadcaster\BroadcasterPermission;
use App\Models\Broadcaster\Broadcaster;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Illuminate\Contracts\Support\Htmlable;

class BroadcasterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::makeIdentitySection(),
            self::makeSubmitPermissionsSection(),
            self::makeConsentSection(),
        ]);
    }

    private static function makeIdentitySection(): Section
    {
        return Section::make()
            ->columns(4)
            ->schema([
                ImageEntry::make('user.avatar')
                    ->getStateUsing(fn (Broadcaster $record) => $record->user->proxiedContentUrl())
                    ->columnSpan(1)
                    ->label('Avatar')
                    ->hiddenLabel()
                    ->circular(),

                Grid::make(4)
                    ->columnSpan(3)
                    ->columns(4)
                    ->schema([
                        TextEntry::make('user.name')
                            ->weight(FontWeight::SemiBold)
                            ->label('Name')
                            ->columnSpan(2),

                        TextEntry::make('id')
                            ->fontFamily(FontFamily::Mono)
                            ->label('Broadcaster ID')
                            ->color('gray'),

                        TextEntry::make('onboarded_at')
                            ->placeholder('Not onboarded')
                            ->label('Onboarded')
                            ->columnSpan(2)
                            ->date(),

                        TextEntry::make('deleted_at')
                            ->hidden(fn ($record): bool => $record->deleted_at === null)
                            ->placeholder('Active')
                            ->label('Deleted')
                            ->color('danger')
                            ->date(),
                    ]),
            ]);
    }

    private static function makeSubmitPermissionsSection(): Section
    {
        return Section::make('Submission Permissions')
            ->columns(3)
            ->schema([
                IconEntry::make('submit_user_allowed')
                    ->label('Users')
                    ->boolean(),

                IconEntry::make('submit_vip_allowed')
                    ->label('VIPs')
                    ->boolean(),

                IconEntry::make('submit_mods_allowed')
                    ->label('Mods')
                    ->boolean(),
            ]);
    }

    private static function makeConsentSection(): Section
    {
        return Section::make('Consent')
            ->columnSpanFull()
            ->columns()
            ->schema([
                TextEntry::make('consent')
                    ->formatStateUsing(fn (BroadcasterConsent $state): Htmlable|string|null => $state->getLabel())
                    ->label('Given Consents')
                    ->placeholder('No consent given')
                    ->color('success')
                    ->separator()
                    ->badge(),

                TextEntry::make('twitch_mod_permissions')
                    ->formatStateUsing(fn (BroadcasterPermission $state): string|Htmlable|null => $state->getLabel())
                    ->placeholder('No permissions granted')
                    ->label('Mod Permissions')
                    ->color('info')
                    ->separator()
                    ->badge(),

                TextEntry::make('default_clip_status'),
            ]);
    }
}
