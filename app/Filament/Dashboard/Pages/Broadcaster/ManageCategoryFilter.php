<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Pages\Broadcaster;

use App\Actions\ImportCategoryAction;
use App\Enums\Broadcaster\DashboardNavigationGroup;
use App\Enums\Broadcaster\DashboardNavigationItem;
use App\Enums\Filament\LucideIcon;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Broadcaster\BroadcasterSubmissionFilter;
use App\Models\Category;
use App\Services\Twitch\Data\CategoryDto;
use App\Services\Twitch\Data\GameDto;
use App\Services\Twitch\Enums\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use BackedEnum;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
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
use Illuminate\Support\Facades\Cache;
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
        // later we can check for permission to this specific page here
        return self::getBroadcaster()?->id === auth()->id();
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
                    ->state(fn (BroadcasterSubmissionFilter $record) => $record->filterable?->proxiedContentUrl())
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
                Select::make('filterable_id')
                    ->getSearchResultsUsing(
                        function (string $search, TwitchService $twitchService) {
                            $search = mb_trim($search);
                            $categories = collect($twitchService->asSessionUser()->searchCategories($search, 100))
                                ->each(fn (CategoryDto $category) => Cache::put("twitch:category:$category->id", $category, now()->addMinutes(30)))
                                ->map(fn (CategoryDto $item): array => ['title' => $item->name, 'id' => $item->id]);

                            $category = Category::where('title', 'ilike', "%$search%")
                                ->whereNotExists(function ($query): void {
                                    $query->from('broadcaster_submission_filters')
                                        ->whereColumn('broadcaster_submission_filters.filterable_id', (new Category)->getTable().'.id')
                                        ->where('broadcaster_submission_filters.filterable_type', $this::getCategoryMorphClass())
                                        ->where('broadcaster_submission_filters.broadcaster_id', $this::getBroadcaster()->id);
                                })
                                ->limit(5)
                                ->pluck('title', 'id')
                                ->map(fn (string $title, int $id): array => ['id' => $id, 'title' => $title])
                                ->merge($categories)
                                ->unique('id')
                                ->take(100);

                            $existingIds = $this->getBaseQuery()
                                ->whereIn('filterable_id', $category->pluck('id'))->pluck('filterable_id')
                                ->map(fn ($id): string => (string) $id)
                                ->all();

                            return $category->reject(fn (array $item): bool => in_array((string) $item['id'], $existingIds, true))
                                ->values()
                                ->sortBy(fn (array $item): int => levenshtein(mb_strtolower($search), mb_strtolower((string) $item['title'])))
                                ->mapWithKeys(fn (array $item): array => [$item['id'] => $item['title']]);
                        })
                    ->getOptionLabelUsing(function (string $value, TwitchService $twitchService, ImportCategoryAction $importCategoryAction) {
                        if ($title = Category::find((int) $value)?->title) {
                            return $title;
                        }

                        if ($category = Cache::get("twitch:category:$value")) {
                            $category = $importCategoryAction->execute($category);

                            return $category->title;
                        }

                        $categories = $twitchService->collection(TwitchEndpoints::GetGames, [
                            'id' => $value,
                        ]);

                        /** @var GameDto $game */
                        $game = array_first($categories);

                        $category = $importCategoryAction->execute($game);

                        return $category->title;
                    })
                    ->label('dashboard/settings/manage-category-filters.table.title')
                    ->translateLabel()
                    ->columnSpanFull()
                    ->searchable()
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
