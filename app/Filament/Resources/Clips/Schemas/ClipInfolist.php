<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\Schemas;

use App\Filament\Infolists\Components\TwitchEmbedEntry;
use Filament\Schemas\Schema;

class ClipInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TwitchEmbedEntry::make('twitch_id')
                    ->label('Twitch Preview')
                    ->columnSpanFull(),
            ]);
    }
}
