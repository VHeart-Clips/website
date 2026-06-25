<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Audits\Schemas;

use App\Models\Audit;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class AuditInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        KeyValueEntry::make('old')
                            ->label('Old')
                            ->state(fn (Audit $record) => collect($record->old ?? [])
                                ->map(fn (array|string|null $value): string|false => is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : (string) $value)
                                ->all()
                            ),

                        KeyValueEntry::make('new')
                            ->label('New')
                            ->state(fn (Audit $record) => collect($record->new ?? [])
                                ->map(fn (array|string|null $value): string|false => is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : (string) $value)
                                ->all()
                            ),
                    ]),
            ])
            ->columns(1);
    }
}
