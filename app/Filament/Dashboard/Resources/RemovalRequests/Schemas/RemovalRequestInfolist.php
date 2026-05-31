<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\RemovalRequests\Schemas;

use App\Enums\Filament\LucideIcon;
use App\Filament\Infolists\Components\TwitchEmbedEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class RemovalRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        TwitchEmbedEntry::make('clip.twitch_id')
                            ->hiddenLabel()
                            ->columnSpan(1),

                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('clip.title')
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->columnSpanFull()
                                    ->hiddenLabel()
                                    ->wrap(),

                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('status')
                                            ->icon(LucideIcon::Clipboard)
                                            ->label('dashboard/resources/removal-requests.infolist.status')
                                            ->translateLabel()
                                            ->badge(),
                                        TextEntry::make('resolved_by.name')
                                            ->icon(LucideIcon::Clipboard)
                                            ->label('dashboard/resources/removal-requests.infolist.resolved_by')
                                            ->translateLabel()
                                            ->badge(),
                                        TextEntry::make('resolved_at')
                                            ->icon(LucideIcon::Clipboard)
                                            ->label('dashboard/resources/removal-requests.infolist.resolved_at')
                                            ->translateLabel()
                                            ->badge(),
                                    ]),

                                TextEntry::make('details')
                                    ->label('dashboard/resources/removal-requests.infolist.details.label')
                                    ->translateLabel()
                                    ->placeholder(__('dashboard/resources/removal-requests.infolist.details.placeholder')),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
