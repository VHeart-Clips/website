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
                            ->label('admin/resources/clips.form.title')
                            ->translateLabel()
                            ->required(),
                        Select::make('category_id')
                            ->label('admin/resources/clips.form.category')
                            ->translateLabel()
                            ->required()
                            ->preload()
                            ->relationship('category', 'title')
                            ->searchable(),
                        TextEntry::make('duration')
                            ->label('admin/resources/clips.form.duration')
                            ->translateLabel()
                            ->formatStateUsing(function ($state): string {
                                $totalSeconds = (int) round($state);

                                $minutes = intdiv($totalSeconds, 60);
                                $seconds = $totalSeconds % 60;

                                return sprintf('%d:%02d', $minutes, $seconds);
                            }),
                        Select::make('broadcaster')
                            ->relationship('broadcaster', 'name')
                            ->disabled()
                            ->label('admin/resources/clips.form.broadcaster')
                            ->translateLabel(),
                        Select::make('creator')
                            ->relationship('creator', 'name')
                            ->disabled()
                            ->label('admin/resources/clips.form.creator')
                            ->translateLabel(),
                        Select::make('submitter')
                            ->relationship('submitter', 'name')
                            ->disabled()
                            ->label('admin/resources/clips.form.submitted_by')
                            ->translateLabel(),
                        DateTimePicker::make('date')
                            ->label('admin/resources/clips.form.created_at')
                            ->translateLabel()
                            ->disabled(),
                    ]),
            ])->columns(3);
    }
}
