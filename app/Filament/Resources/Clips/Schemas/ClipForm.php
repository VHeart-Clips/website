<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\Schemas;

use App\Filament\Infolists\Components\TwitchEmbedEntry;
use App\Services\Twitch\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClipForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        TextInput::make('url')
                            ->url(),
                        TextInput::make('thumbnail_url')
                            ->url(),
                        TextInput::make('vod_id')
                            ->numeric(),
                        TextInput::make('vod_offset')
                            ->numeric(),
                        TextInput::make('duration')
                            ->required()
                            ->numeric(),
                        TextInput::make('status'),
                        TextInput::make('language'),
                        Toggle::make('is_anonymous')
                            ->required(),
                        DateTimePicker::make('date')
                            ->required(),
                    ])->columnSpan(2),
                Section::make()
                    ->schema([
                        TwitchEmbedEntry::make('twitch_id')
                            ->hiddenLabel()
                            ->alignCenter(),
                        TextEntry::make('broadcaster.name')->label('Broadcaster'),
                        TextEntry::make('creator.name')->label('Clip Creator'),
                        TextEntry::make('submitter.name')->label('Submitted By'),
                        Select::make('game_id')
                            ->label('Twitch Category')
                            ->required()
                            ->preload()
                            ->relationship('game', 'title')
                            ->searchable(),
                    ]),
            ])->columns(3);
    }
}
