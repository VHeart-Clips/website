<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Pages\Broadcaster;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Broadcaster\DashboardNavigationGroup;
use App\Enums\Broadcaster\DashboardNavigationItem;
use App\Enums\Filament\LucideIcon;
use App\Filament\Resources\Categories\CategorySelect;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Broadcaster\BroadcasterSubmissionFilter;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use UnitEnum;

class ManageCategoryFilter extends Page implements HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static string|null|BackedEnum $navigationIcon = LucideIcon::Folder;

    protected static ?int $navigationSort = 1000;

    protected static string|null|UnitEnum $navigationGroup = DashboardNavigationGroup::Settings;

    protected string $view = 'filament.dashboard.pages.broadcaster.manage-category-filter';

    protected ?string $heading = '';

    public static function getNavigationLabel(): string
    {
        return DashboardNavigationItem::ManageCategoryFilter->getLabel();
    }

    public static function canAccess(): bool
    {
        return Gate::allows('dashboardAccess', [Filament::getTenant(), BroadcasterPermission::CategoryFilter]);
    }

    public static function getBroadcaster(): ?Broadcaster
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Broadcaster ? $tenant : null;
    }

    public function getTitle(): string|Htmlable
    {
        return self::getBroadcaster()->name.' - '.DashboardNavigationItem::ManageCategoryFilter->getLabel();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
            ->columns([
                ImageColumn::make('box_art')
                    ->state(fn (BroadcasterSubmissionFilter $record) => $record->filterable instanceof Category ? $record->filterable->getBoxArt() : null)
                    ->label('')
                    ->imageHeight(100)
                    ->grow(false)
                    ->width(75),
                TextColumn::make('filterable.title')
                    ->searchable(query: fn (Builder $query, string $search) => $query->whereHasMorph(
                        'filterable',
                        Category::class,
                        fn (Builder $q) => $q->where('title', 'ilike', "%{$search}%"),
                    ))
                    ->label('dashboard/settings/manage-category-filters.table.title')
                    ->translateLabel(),
                ToggleColumn::make('state')
                    ->label('dashboard/settings/manage-category-filters.table.state')
                    ->translateLabel()
                    ->onIcon(LucideIcon::Check)
                    ->offIcon(LucideIcon::X)
                    ->onColor('success')
                    ->offColor('danger')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('state')
                    ->label('dashboard/settings/manage-category-filters.filters.state.label')
                    ->translateLabel()
                    ->placeholder(__('dashboard/settings/manage-category-filters.filters.state.placeholder'))
                    ->trueLabel(__('dashboard/settings/manage-category-filters.filters.state.true'))
                    ->falseLabel(__('dashboard/settings/manage-category-filters.filters.state.false'))
                    ->queries(
                        true: fn (Builder $q) => $q->where('state', true),
                        false: fn (Builder $q) => $q->where('state', false),
                    ),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->heading(DashboardNavigationItem::ManageCategoryFilter->getLabel())
            ->description(__('dashboard/settings/manage-category-filters.section.description'))
            ->toolbarActions([
                $this->makeCreateAction(),
                DeleteBulkAction::make(),
            ])
            ->modelLabel(__('dashboard/settings/manage-category-filters.section.model.singular'))
            ->pluralModelLabel(__('dashboard/settings/manage-category-filters.section.model.plural'));
    }

    private static function getCategoryMorphClass(): string
    {
        return (new Category)->getMorphClass();
    }

    private function makeCreateAction(): CreateAction
    {
        return CreateAction::make()
            ->schema([
                CategorySelect::make('filterable_id')
                    ->whereNotExists(function ($query): void {

                        $query->from('broadcaster_submission_filters')
                            ->whereColumn('broadcaster_submission_filters.filterable_id', (new Category)->getTable().'.id')
                            ->where('broadcaster_submission_filters.filterable_type', $this::getCategoryMorphClass())
                            ->where('broadcaster_submission_filters.broadcaster_id', $this::getBroadcaster()->id);
                    })->ignoredIds(fn (Collection $category): array => $this->getBaseQuery()
                    ->whereIn('filterable_id', $category->pluck('id'))->pluck('filterable_id')
                    ->map(fn ($id): string => (string) $id)
                    ->all())
                    ->label('dashboard/settings/manage-category-filters.table.title')
                    ->translateLabel()
                    ->columnSpanFull()
                    ->required(),
                Toggle::make('state')
                    ->label('dashboard/settings/manage-category-filters.table.state')
                    ->translateLabel()
                    ->onIcon(LucideIcon::Check)
                    ->offIcon(LucideIcon::X)
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->mutateDataUsing(function (array $data): array {
                $data['broadcaster_id'] = $this::getBroadcaster()->id;
                $data['filterable_type'] = $this::getCategoryMorphClass();

                return $data;
            });
    }

    private function getBaseQuery(): Builder
    {
        $tenant = $this::getBroadcaster();

        return $tenant->filters()->getQuery()->where('filterable_type', $this::getCategoryMorphClass());
    }
}
