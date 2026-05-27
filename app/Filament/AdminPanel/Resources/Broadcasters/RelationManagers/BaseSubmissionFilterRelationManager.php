<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters\RelationManagers;

use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Broadcasters\Pages\ViewBroadcaster;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Broadcaster\BroadcasterSubmissionFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseSubmissionFilterRelationManager extends RelationManager
{
    protected static string $relationship = 'filters';

    protected static bool $isLazy = false;

    abstract protected function getMorphClass(): string;

    abstract protected function getFilterableColumns(): array;

    abstract protected function getFilterableFormField(): mixed;

    /**
     * @param  Broadcaster  $ownerRecord
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('viewAny', [BroadcasterSubmissionFilter::class, $ownerRecord]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('filterable_type', $this->getMorphClass())->with(['filterable']))
            ->deferLoading()
            ->columns([
                ...$this->getFilterableColumns(),

                ToggleColumn::make('state')
                    ->label('Allowed')
                    ->onIcon(LucideIcon::Check)
                    ->offIcon(LucideIcon::X)
                    ->onColor('success')
                    ->offColor('danger')
                    ->disabled(fn (): bool => $this->getPageClass() === ViewBroadcaster::class)
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->authorize('create', $this->getOwnerRecord())
                    ->mutateDataUsing(function (array $data): array {
                        $data['filterable_type'] = $this->getMorphClass();

                        return $data;
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorize('deleteAny', $this->getOwnerRecord()),
                ]),
            ]);
    }

    protected function sharedFormFields(): array
    {
        return [
            Toggle::make('state')
                ->onIcon(LucideIcon::Check)
                ->offIcon(LucideIcon::X)
                ->onColor('success')
                ->offColor('danger')
                ->onColor('success')
                ->label('Allowed')
                ->required(),
        ];
    }
}
