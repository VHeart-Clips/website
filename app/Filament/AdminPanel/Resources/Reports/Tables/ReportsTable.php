<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Reports\Tables;

use App\Enums\Filament\LucideIcon;
use App\Enums\Reports\ReportReason;
use App\Enums\Reports\ReportStatus;
use App\Enums\Reports\ResolveAction;
use App\Filament\Actions\ResourceLinkAction;
use App\Filament\Tables\MorphColumn;
use App\Models\Report;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'reportable' => fn ($q) => $q->withTrashed(),
                'reporter' => fn ($q) => $q->withTrashed(),
            ]))
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('reason')
                    ->wrap()
                    ->limit(50)
                    ->badge()
                    ->sortable(),

                MorphColumn::make('reportable')
                    ->placeholder('Deleted :('),

                MorphColumn::make('reporter')
                    ->label('Reported By'),

                MorphColumn::make('claimer')
                    ->label('Claimed By')
                    ->placeholder('Unclaimed'),

                TextColumn::make('resolve_action')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->description(fn (Report $record) => $record->created_at->toFormattedDayDateString())
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ReportStatus::class),

                SelectFilter::make('reason')
                    ->options(ReportReason::class),

                SelectFilter::make('resolve_action')
                    ->options(ResolveAction::class),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ResourceLinkAction::make()
                        ->relationship('reporter')
                        ->label('View Reporter'),
                    ResourceLinkAction::make()
                        ->relationship('reportable')
                        ->label('View Reportable'),
                ])
                    ->label('View')
                    ->link()
                    ->icon(LucideIcon::ExternalLink),
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
