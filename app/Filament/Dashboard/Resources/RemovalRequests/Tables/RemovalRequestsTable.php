<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\RemovalRequests\Tables;

use App\Enums\Broadcaster\RemovalRequestStatus;
use App\Filament\Tables\MorphColumn;
use App\Models\Broadcaster\RemovalRequest;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RemovalRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                MorphColumn::make('clip'),
                TextColumn::make('status')
                    ->label('dashboard/resources/removal-requests.table.columns.status')
                    ->translateLabel(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('dashboard/resources/removal-requests.table.columns.status')
                    ->translateLabel()
                    ->options(RemovalRequestStatus::class)
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth(Width::SixExtraLarge),
                DeleteAction::make()
                    ->disabled(fn (RemovalRequest $record): bool => $record->status !== RemovalRequestStatus::Pending || $record->claimed_by !== null),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
