<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters\RelationManagers;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Filament\Resources\Users\UserSelect;
use App\Filament\Tables\MorphColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
use Filament\Support\Enums\Operation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                UserSelect::make('user_id')
                    ->required()
                    ->columnSpanFull()
                    ->label('User')
                    ->whereNotExists(function ($query): void {
                        $query->from('broadcaster_team_members')
                            ->whereColumn('broadcaster_team_members.user_id', 'users.id')
                            ->where('broadcaster_team_members.broadcaster_id', $this->getOwnerRecord()->id)
                            ->where('id', '!=', $this->getOwnerRecord()->id);
                    })
                    ->ignoredIds(fn (Collection $user) => $this->getOwnerRecord()
                        ->members()
                        ->getQuery()
                        ->whereIn('user_id', $user->pluck('id'))
                        ->pluck('user_id')
                        ->merge([$this->getOwnerRecord()->id])
                        ->all())
                    ->disabledOn(Operation::Edit),
                CheckboxList::make('permissions')
                    ->gridDirection(GridDirection::Row)
                    ->columns(2)
                    ->options(BroadcasterPermission::class)
                    ->columnSpanFull()
                    ->bulkToggleable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                MorphColumn::make('user')
                    ->placeholder('User'),
                TextColumn::make('permissions')
                    ->formatStateUsing(fn (BroadcasterPermission $state): string|Htmlable|null => $state->getLabel())
                    ->label('Permissions')
                    ->color('info')
                    ->separator()
                    ->badge(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorizeIndividualRecords(),
                ]),
            ]);
    }
}
