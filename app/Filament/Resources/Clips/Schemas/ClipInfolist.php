<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\Schemas;

use App\Filament\Infolists\Components\TwitchEmbedEntry;
use App\Filament\Resources\Users\UserResource;
use App\Models\Clip;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClipInfolist
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
                        TextEntry::make('title')
                            ->label('admin/resources/clips.infolist.title')
                            ->translateLabel(),
                        TextEntry::make('game.title')
                            ->label('admin/resources/clips.infolist.category')
                            ->translateLabel(),
                        TextEntry::make('duration')
                            ->label('admin/resources/clips.infolist.duration')
                            ->translateLabel()
                            ->formatStateUsing(function ($state) {
                                $totalSeconds = (int) round($state);

                                $minutes = intdiv($totalSeconds, 60);
                                $seconds = $totalSeconds % 60;

                                return sprintf('%d:%02d', $minutes, $seconds);
                            }),
                        TextEntry::make('broadcaster.name')
                            ->label('admin/resources/clips.infolist.broadcaster')
                            ->translateLabel()
                            ->url(function (Clip $clip) {
                                if (! $clip->broadcaster?->exists) {
                                    return null;
                                }

                                return UserResource::getUrl('view', ['record' => $clip->broadcaster]);
                            })->openUrlInNewTab(),
                        TextEntry::make('creator.name')
                            ->label('admin/resources/clips.infolist.clipper')
                            ->translateLabel()
                            ->url(function (Clip $clip) {
                                if (! $clip->creator?->exists) {
                                    return null;
                                }

                                return UserResource::getUrl('view', ['record' => $clip->creator]);
                            })->openUrlInNewTab(),
                        TextEntry::make('submitter.name')
                            ->label('admin/resources/clips.infolist.submitted_by')
                            ->translateLabel()
                            ->url(function (Clip $clip) {
                                if (! $clip->submitter?->exists) {
                                    return null;
                                }

                                return UserResource::getUrl('view', ['record' => $clip->submitter]);
                            })->openUrlInNewTab(),
                        TextEntry::make('created_at')
                            ->date()
                            ->label('admin/resources/clips.infolist.submitted_at')
                            ->translateLabel()
                            ->disabled(),
                        TextEntry::make('date')
                            ->date()
                            ->label('admin/resources/clips.infolist.created_at')
                            ->translateLabel()
                            ->disabled(),
                    ]),
            ])->columns(3);
    }
}
