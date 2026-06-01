<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters\RelationManagers;

use App\Models\Broadcaster\BroadcasterSubmissionFilter;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class CategoryFiltersRelationManager extends BaseSubmissionFilterRelationManager
{
    protected static ?string $title = 'Category Submission Filters';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFilterableFormField(),
            ...$this->sharedFormFields(),
        ]);
    }

    protected function getMorphClass(): string
    {
        return (new Category)->getMorphClass();
    }

    protected function getFilterableColumns(): array
    {
        return [
            ImageColumn::make('box_art')
                ->state(fn (BroadcasterSubmissionFilter $record): ?string => $record->filterable instanceof Category ? $record->filterable->getBoxArt() : null)
                ->label('')
                ->imageHeight(100)
                ->grow(false)
                ->translateLabel()
                ->width(75),

            TextColumn::make('filterable.title')
                ->searchable(query: fn (Builder $query, string $search) => $query->whereHasMorph(
                    'filterable',
                    Category::class,
                    fn (Builder $q) => $q->where('title', 'ilike', "%{$search}%"),
                ))
                ->label('admin/resources/categories.table.columns.title')
                ->translateLabel(),
        ];
    }

    protected function getFilterableFormField(): mixed
    {
        return Select::make('filterable_id')
            ->getSearchResultsUsing(
                fn (string $search) => Category::where('title', 'ilike', "%{$search}%")
                    ->whereNotExists(function ($query): void {
                        $query->from('broadcaster_submission_filters')
                            ->whereColumn('broadcaster_submission_filters.filterable_id', (new Category)->getTable().'.id')
                            ->where('broadcaster_submission_filters.filterable_type', $this->getMorphClass())
                            ->where('broadcaster_submission_filters.broadcaster_id', $this->getOwnerRecord()->id);
                    })
                    ->limit(5)
                    ->pluck('title', 'id')
            )
            ->options(fn () => Category::query()
                ->whereNotExists(function ($query): void {
                    $query->from('broadcaster_submission_filters')
                        ->whereColumn('broadcaster_submission_filters.filterable_id', (new Category)->getTable().'.id')
                        ->where('broadcaster_submission_filters.filterable_type', $this->getMorphClass())
                        ->where('broadcaster_submission_filters.broadcaster_id', $this->getOwnerRecord()->id);
                })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->pluck('title', 'id')
            )
            ->getOptionLabelUsing(fn (string $value) => Category::find((int) $value)?->title)
            ->label('Category')
            ->columnSpanFull()
            ->searchable()
            ->required();
    }
}
