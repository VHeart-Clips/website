<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters\RelationManagers;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class UserFiltersRelationManager extends BaseSubmissionFilterRelationManager
{
    protected static ?string $title = 'User Submission Filters';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFilterableFormField(),
            ...$this->sharedFormFields(),
        ]);
    }

    protected function getMorphClass(): string
    {
        return (new User)->getMorphClass();
    }

    protected function getFilterableColumns(): array
    {
        return [
            ImageColumn::make('filterable.avatar_url')
                ->grow(false)
                ->imageSize(32)
                ->label('')
                ->square(),

            TextColumn::make('filterable.name')
                ->searchable(query: fn (Builder $query, string $search) => $query->whereHasMorph(
                    'filterable',
                    User::class,
                    fn (Builder $q) => $q->where('name', 'ilike', "%{$search}%"),
                ))
                ->label('User'),
        ];
    }

    protected function getFilterableFormField(): mixed
    {
        return Select::make('filterable_id')
            ->getSearchResultsUsing(
                fn (string $search) => User::where('name', 'ilike', "%{$search}%")
                    ->whereNotExists(function ($query): void {
                        $query->from('broadcaster_submission_filters')
                            ->whereColumn('broadcaster_submission_filters.filterable_id', (new User)->getTable().'.id')
                            ->where('broadcaster_submission_filters.filterable_type', $this->getMorphClass())
                            ->where('broadcaster_submission_filters.broadcaster_id', $this->getOwnerRecord()->id);
                    })
                    ->limit(5)
                    ->pluck('name', 'id')
            )
            ->options(fn () => User::query()
                ->whereNotExists(function ($query): void {
                    $query->from('broadcaster_submission_filters')
                        ->whereColumn('broadcaster_submission_filters.filterable_id', (new User)->getTable().'.id')
                        ->where('broadcaster_submission_filters.filterable_type', $this->getMorphClass())
                        ->where('broadcaster_submission_filters.broadcaster_id', $this->getOwnerRecord()->id);
                })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->pluck('name', 'id')
            )
            ->getOptionLabelUsing(fn (string $value) => User::find((int) $value)?->name)
            ->label('User')
            ->hiddenOn(Operation::Edit)
            ->columnSpanFull()
            ->searchable()
            ->required();
    }
}
