<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Pages\Broadcaster;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Broadcaster\DashboardNavigationGroup;
use App\Enums\Broadcaster\DashboardNavigationItem;
use App\Enums\FeatureFlag;
use App\Enums\Filament\LucideIcon;
use App\Filament\Dashboard\Traits\DisabledNavigationUntilOnboarding;
use App\Filament\Resources\Users\UserSelect;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use App\Support\FeatureFlag\Feature;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use UnitEnum;

class ManageUserFilter extends Page implements HasTable
{
    use DisabledNavigationUntilOnboarding;
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static string|null|BackedEnum $navigationIcon = LucideIcon::Users;

    protected static ?int $navigationSort = 1000;

    protected static string|null|UnitEnum $navigationGroup = DashboardNavigationGroup::Settings;

    protected string $view = 'filament.dashboard.pages.broadcaster.manage-user-filter';

    protected ?string $heading = '';

    public static function getNavigationLabel(): string
    {
        return DashboardNavigationItem::ManageUserFilter->getLabel();
    }

    public static function canAccess(): bool
    {
        return Feature::isActive(FeatureFlag::BroadcasterUserSubmissionFilterManager) && Gate::allows('dashboardAccess', [Filament::getTenant(), BroadcasterPermission::UserFilter]);
    }

    public function getTitle(): string|Htmlable
    {
        return Filament::getTenant()->name.' - '.DashboardNavigationItem::ManageUserFilter->getLabel();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
            ->columns([
                TextColumn::make('filterable.name')
                    ->searchable(query: fn (Builder $query, string $search) => $query->whereHasMorph(
                        'filterable',
                        User::class,
                        fn (Builder $q) => $q->where('name', 'ilike', "%{$search}%"),
                    ))
                    ->label('dashboard/settings/manage-user-filters.table.name')
                    ->translateLabel(),
                ToggleColumn::make('state')
                    ->label('dashboard/settings/manage-user-filters.table.state')
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
                    ->label('dashboard/settings/manage-user-filters.filters.state.label')
                    ->translateLabel()
                    ->placeholder(__('dashboard/settings/manage-user-filters.filters.state.placeholder'))
                    ->trueLabel(__('dashboard/settings/manage-user-filters.filters.state.true'))
                    ->falseLabel(__('dashboard/settings/manage-user-filters.filters.state.false'))
                    ->queries(
                        true: fn (Builder $q) => $q->whereState(true),
                        false: fn (Builder $q) => $q->whereState(false),
                    ),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->heading(DashboardNavigationItem::ManageUserFilter->getLabel())
            ->description(__('dashboard/settings/manage-user-filters.section.description'))
            ->modelLabel(__('dashboard/settings/manage-user-filters.section.model.singular'))
            ->pluralModelLabel(__('dashboard/settings/manage-user-filters.section.model.plural'))
            ->toolbarActions([
                CreateAction::make()
                    ->schema([
                        UserSelect::make('filterable_id')
                            ->columnSpanFull()
                            ->rules([
                                Rule::unique('broadcaster_submission_filters', 'filterable_id')
                                    ->where('filterable_type', $this->getMorphClass())
                                    ->where('broadcaster_id', $this->getOwnerRecord()->id),
                                Rule::notIn([$this->getOwnerRecord()->id]),
                            ])
                            ->validationMessages([
                                'unique' => __('dashboard/settings/manage-user-filters.forms.create.user-select.rules.unique'),
                                'not_in' => __('dashboard/settings/manage-user-filters.forms.create.user-select.rules.not_in'),
                            ])
                            ->required(),

                        Toggle::make('state')
                            ->label('dashboard/settings/manage-user-filters.table.state')
                            ->translateLabel()
                            ->onIcon(LucideIcon::Check)
                            ->offIcon(LucideIcon::X)
                            ->onColor('success')
                            ->offColor('danger'),
                    ])
                    ->mutateDataUsing(function (array $data): array {
                        $data['broadcaster_id'] = $this->getOwnerRecord()->id;
                        $data['filterable_type'] = $this->getMorphClass();

                        return $data;
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return Broadcaster
     */
    public function getOwnerRecord(): Model
    {
        return Filament::getTenant();
    }

    private function getMorphClass(): string
    {
        return (new User)->getMorphClass();
    }

    private function getBaseQuery(): Builder
    {
        $tenant = $this->getOwnerRecord();

        return $tenant->filters()->getQuery()->where('filterable_type', $this->getMorphClass());
    }
}
