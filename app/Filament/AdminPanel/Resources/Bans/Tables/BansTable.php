<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Bans\Tables;

use App\Filament\Tables\MorphColumn;
use App\Models\Ban;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class BansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with([
                    'bannedBy',
                    'unbannedBy',
                    'bannable',
                ])
                ->withoutGlobalScope(SoftDeletingScope::class)
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                MorphColumn::make('bannedBy')->placeholder(fn (Ban $ban): string => $ban->admin_id ? 'User#'.$ban->admin_id : 'System'),
                MorphColumn::make('unbannedBy')->placeholder(fn (Ban $ban): string => $ban->unbanned_by ? 'User#'.$ban->unbanned_by : '-'),
                MorphColumn::make('bannable')->placeholder(fn (Ban $ban): string => $ban->bannable_type ? Str::title($ban->bannable_type).'#'.$ban->bannable_id : '-'),

                TextColumn::make('banned_until')
                    ->placeholder('Permanent')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('unbanned_at')
                    ->placeholder('-')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('status')
                    ->label('Status')
                    ->translateLabel()
                    ->trueLabel('All')
                    ->falseLabel(__('Expired Only'))
                    ->placeholder('Active Only')
                    ->queries(
                        true: fn (Builder $query): Builder => $query,
                        false: fn (Builder $query): Builder => $query->whereExpired(),
                        blank: fn (Builder $query): Builder => $query->whereActive(),
                    ),
                TernaryFilter::make('permanent')
                    ->label('Permanent')
                    ->translateLabel()
                    ->trueLabel('Permanent Only')
                    ->falseLabel(__('Temporary Only'))
                    ->placeholder('All')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->wherePermanent(),
                        false: fn (Builder $query): Builder => $query->whereTemporary(),
                    ),

                SelectFilter::make('bannedBy')
                    ->relationship(
                        'bannedBy',
                        'name',
                        fn (Builder $query) => $query->whereIn(
                            column: 'id',
                            values: Ban::select('admin_id')->distinct()->whereNotNull('admin_id')
                        )
                    )
                    ->optionsLimit(10)
                    ->searchable()
                    ->preload(),

                SelectFilter::make('unbannedBy')
                    ->relationship(
                        'unbannedBy',
                        'name',
                        fn (Builder $query) => $query->whereIn(
                            column: 'id',
                            values: Ban::select('unbanned_by')->distinct()->whereNotNull('unbanned_by')
                        )
                    )
                    ->optionsLimit(10)
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
