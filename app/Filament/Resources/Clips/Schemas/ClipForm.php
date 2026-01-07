<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\Schemas;

use App\Filament\Infolists\Components\TwitchEmbedEntry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                        TwitchEmbedEntry::make('twitch_id')
                            ->hiddenLabel()
                            ->alignCenter(),
                    ])->columnSpan(2),
                Section::make()
                    ->compact()
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        Select::make('game_id')
                            ->label('Twitch Category')
                            ->required()
                            ->preload()
                            ->relationship('game', 'title')
                            ->searchable(),
                        TextEntry::make('duration')
                            ->formatStateUsing(function ($state) {
                                $totalSeconds = (int) round($state);

                                $minutes = intdiv($totalSeconds, 60);
                                $seconds = $totalSeconds % 60;

                                return sprintf('%d:%02d', $minutes, $seconds);
                            }),
                        Select::make('broadcaster')->relationship('broadcaster', 'name')->disabled()->label('Broadcaster'),
                        Select::make('creator')->relationship('creator', 'name')->disabled()->label('Clip Creator'),
                        Select::make('submitter')->relationship('submitter', 'name')->disabled()->label('Submitted By'),
                        DateTimePicker::make('date')
                            ->label('Clip Created At')
                            ->disabled(),
                    ]),
            ])->columns(3);
    }
}
