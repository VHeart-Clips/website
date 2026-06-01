<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters\RelationManagers;

use App\Filament\Resources\Users\UserSelect;
use App\Models\Broadcaster\BroadcasterSubmissionFilter;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

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
        return UserSelect::make('filterable_id')
            ->rules([
                Rule::unique('broadcaster_submission_filters', 'filterable_id')
                    ->where('filterable_type', $this->getMorphClass())
                    ->where('broadcaster_id', $this->getOwnerRecord()->id),
                Rule::notIn([$this->getOwnerRecord()->id]),
            ])
            ->validationMessages([
                'unique' => 'user already in filter list',
                'not_in' => 'broadcaster can not be added to their own filter list lol',
            ])
            ->hiddenOn(Operation::Edit)
            ->columnSpanFull()
            ->required();
    }
}
