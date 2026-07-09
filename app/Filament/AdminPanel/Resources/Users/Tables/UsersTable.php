<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\Tables;

use App\Filament\AdminPanel\Actions\Ban\BanAction;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table): void {
                $sortColumn = $table->getSortColumn();
                $showCount = $sortColumn === 'votes_count' || ! $table->getColumn('votes_count')->isToggledHidden();
                $showDay = $sortColumn === 'votes_per_day' || ! $table->getColumn('votes_per_day')->isToggledHidden();
                $showWeek = $sortColumn === 'votes_per_week' || ! $table->getColumn('votes_per_week')->isToggledHidden();

                $avgVotes = static fn (string $interval): Builder => User::query()
                    ->selectRaw('round(coalesce(avg(interval_groups.count), 0), 2)')
                    ->fromSub(fn (BuilderContract $subQuery): BuilderContract => $subQuery
                        ->selectRaw('count(*) as count')
                        ->from('votes')
                        ->whereColumn('votes.user_id', 'users.id')
                        ->groupByRaw("date_trunc('$interval', votes.created_at)"), 'interval_groups');

                $query
                    ->select('users.*')
                    ->whereNot('users.id', 0);

                if ($showCount) {
                    $query->withCount('votes');
                } else {
                    $query->selectSub('0', 'votes_count');
                }

                $query
                    ->selectSub($showDay ? $avgVotes('day') : '0', 'votes_per_day')
                    ->selectSub($showWeek ? $avgVotes('week') : '0', 'votes_per_week');
            })
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                ImageColumn::make('avatar_url')
                    ->label('Avatar')->square(),
                TextColumn::make('name')
                    ->searchable()
                    ->label('Name'),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->toggleable()
                    ->badge(),
                TextColumn::make('votes_count')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('votes_per_day')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Votes per Day')
                    ->sortable(),

                TextColumn::make('votes_per_week')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Votes per Week')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('roles')
                    ->preload()
                    ->relationship('roles', 'name')
                    ->multiple(),

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                BanAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make()->label('Restore User'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords()
                        ->label('Disable Users'),
                    ForceDeleteBulkAction::make()
                        ->authorizeIndividualRecords(),
                    RestoreBulkAction::make()
                        ->authorizeIndividualRecords()
                        ->label('Restore Users'),
                ]),
            ]);
    }
}
