<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\RelationManagers;

use App\Enums\Clips\CompilationClipStatus;
use App\Filament\Resources\Compilations\CompilationResource;
use Filament\Actions\AttachAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompilationsRelationManager extends RelationManager
{
    protected static string $relationship = 'compilations';

    protected static ?string $relatedResource = CompilationResource::class;

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('admin/resources/clips.table.columns.title')
                    ->translateLabel()
                    ->wrap()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('admin/resources/compilations.form.type')
                    ->translateLabel()
                    ->badge(),

                TextColumn::make('pivot.claimer.name')
                    ->label('admin/resources/compilations.relation_managers.clips.columns.claimer')
                    ->translateLabel(),

                TextColumn::make('pivot.status')
                    ->label('admin/resources/compilations.relation_managers.clips.columns.status')
                    ->translateLabel()
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('admin/resources/clips.table.columns.created_at')
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('status')
                            ->label('admin/resources/compilations.relation_managers.clips.columns.status')
                            ->translateLabel()
                            ->options(CompilationClipStatus::class)
                            ->default(CompilationClipStatus::Pending)
                            ->required(),
                    ]),
            ]);
    }
}
