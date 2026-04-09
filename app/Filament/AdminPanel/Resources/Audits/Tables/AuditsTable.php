<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Audits\Tables;

use App\Enums\Filament\LucideIcon;
use App\Filament\Filters\DateRangeFilter;
use App\Models\Audit;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('causer_type')
                    ->label('Actor Type')
                    ->color('gray')
                    ->badge(),

                TextColumn::make('causer_id')
                    ->label('Actor ID')
                    ->color('gray'),

                TextColumn::make('auditable_type')
                    ->label('Resource')
                    ->color('info')
                    ->badge(),

                TextColumn::make('auditable_id')
                    ->label('Resource ID')
                    ->color('gray'),

                TextColumn::make('event')
                    ->color(fn (string $state): string => match ($state) {
                        'created', 'restored' => 'success',
                        'updated' => 'warning',
                        'deleted', 'forceDeleted' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): LucideIcon => match ($state) {
                        'created' => LucideIcon::PlusCircle,
                        'updated' => LucideIcon::Pencil,
                        'deleted' => LucideIcon::Trash,
                        default => LucideIcon::CircleQuestionMark,
                    })
                    ->badge(),

                TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('IP Address')
                    ->copyMessage('IP copied')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('user_agent')
                    ->tooltip(fn (TextColumn $column): ?string => mb_strlen((string) $column->getState()) > 40 ? $column->getState() : null)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('User Agent')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('request_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->fontFamily('mono')
                    ->label('Request ID')
                    ->limit(20)
                    ->searchable()
                    ->copyable(),

                TextColumn::make('created_at')
                    ->tooltip(fn ($record) => $record->created_at->format('d.m.Y H:i:s'))
                    ->dateTime('d. M Y, H:i')
                    ->label('Timestamp')
                    ->sortable()
                    ->since(),

                TextColumn::make('updated_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                self::makeEventFilter(),
                self::makeCauserFilter(),
                self::makeAuditableFilter(),
                self::makeSystemFilter(),
                DateRangeFilter::make('created_at'),
            ])
            ->recordActions([
                ViewAction::make()->modalWidth(Width::ScreenTwoExtraLarge),
            ])
            ->filtersFormWidth(Width::ExtraLarge);
    }

    private static function makeSystemFilter(): TernaryFilter
    {
        return TernaryFilter::make('system_actor')
            ->label('Actor')
            ->placeholder('All')
            ->trueLabel('System only')
            ->falseLabel('Users only')
            ->queries(
                true: fn (Builder $query) => $query->where(
                    fn (Builder $q) => $q->whereNull('causer_id')->orWhere('causer_id', 0)
                ),
                false: fn (Builder $query) => $query->whereNotNull('causer_id')->where('causer_id', '!=', 0),
            );
    }

    private static function makeEventFilter(): SelectFilter
    {
        return SelectFilter::make('event')
            ->label('Event')
            ->multiple()
            ->options(fn (): array => Audit::query()
                ->select(['event'])
                ->distinct()
                ->orderBy('event')
                ->pluck('event', 'event')
                ->all()
            );
    }

    private static function makeAuditableFilter(): SelectFilter
    {
        return SelectFilter::make('auditable_type')
            ->label('Resource')
            ->multiple()
            ->options(fn (): array => Audit::query()
                ->select(['id', 'auditable_type', 'auditable_id'])
                ->distinct()
                ->orderBy('auditable_type')
                ->pluck('auditable_type', 'auditable_type')
                ->all()
            );
    }

    private static function makeCauserFilter(): SelectFilter
    {
        return SelectFilter::make('causer_type')
            ->label('Actor Type')
            ->multiple()
            ->options(fn (): array => Audit::query()
                ->select(['id', 'causer_type', 'causer_type'])
                ->distinct()
                ->whereNotNull('causer_type')
                ->orderBy('causer_type')
                ->pluck('causer_type', 'causer_type')
                ->all()
            );
    }
}
