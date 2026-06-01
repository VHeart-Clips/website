<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Pages\Broadcaster;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Broadcaster\DashboardNavigationGroup;
use App\Enums\Broadcaster\DashboardNavigationItem;
use App\Enums\FeatureFlag;
use App\Enums\Filament\LucideIcon;
use App\Filament\Resources\Users\UserSelect;
use App\Models\Broadcaster\Broadcaster;
use App\Support\FeatureFlag\Feature;
use BackedEnum;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use UnitEnum;

class ManageTeamMember extends Page implements HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public ?array $twitchModsFormData = [];

    protected static string|null|BackedEnum $navigationIcon = LucideIcon::Users;

    protected static ?int $navigationSort = 999;

    protected static string|null|UnitEnum $navigationGroup = DashboardNavigationGroup::Settings;

    protected string $view = 'filament.dashboard.pages.broadcaster.manage-team-member';

    protected ?string $heading = '';

    public static function getNavigationLabel(): string
    {
        return DashboardNavigationItem::ManageTeamMember->getLabel();
    }

    public static function canAccess(): bool
    {
        return Feature::isActive(FeatureFlag::BroadcasterTenant) && Gate::allows('dashboardAccess', [Filament::getTenant()]);
    }

    public static function getBroadcaster(): ?Broadcaster
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Broadcaster ? $tenant : null;
    }

    public function getTitle(): string|Htmlable
    {
        return self::getBroadcaster()->name.' - '.DashboardNavigationItem::ManageTeamMember->getLabel();
    }

    public function mount(): void
    {
        $this->twitchModsForm->fill(
            collect(BroadcasterPermission::cases())
                ->mapWithKeys(fn (BroadcasterPermission $case): array => [
                    "permission_{$case->value}" => self::getBroadcaster()->twitch_mod_permissions?->contains(fn (BroadcasterPermission $c): bool => $c === $case) ?? false,
                ])
                ->all()
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
            ->columns([
                ImageColumn::make('user.avatar_url')
                    ->imageHeight(30)
                    ->circular()
                    ->label('')
                    ->grow(false)
                    ->Width(30),
                TextColumn::make('user.name')
                    ->label('dashboard/settings/manage-team-member.table.user')
                    ->translateLabel()->searchable(),
                TextColumn::make('permissions')
                    ->formatStateUsing(fn (BroadcasterPermission $state): string|Htmlable|null => $state->getLabel())
                    ->label('dashboard/settings/manage-team-member.table.permissions')
                    ->translateLabel()
                    ->color('info')
                    ->separator()
                    ->badge(),
            ])
            ->filters([

            ])
            ->recordActions([
                $this->makeEditAction(),
                DeleteAction::make(),
            ])
            ->heading(DashboardNavigationItem::ManageTeamMember->getLabel())
            ->description(__('dashboard/settings/manage-team-member.section.description'))
            ->toolbarActions([
                $this->makeCreateAction(),
                DeleteBulkAction::make(),
            ])
            ->modelLabel(__('dashboard/settings/manage-team-member.section.model.singular'))
            ->pluralModelLabel(__('dashboard/settings/manage-team-member.section.model.plural'));
    }

    public function twitchModsForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('twitch_mod_permissions')
                ->heading(__('dashboard/settings/manage-team-member.sections.twitch_mod_permissions.label'))
                ->description(__('dashboard/settings/manage-team-member.sections.twitch_mod_permissions.description'))
                ->schema([
                    Form::make(
                        collect(BroadcasterPermission::cases())
                            ->map(fn (BroadcasterPermission $case): Toggle => Toggle::make("permission_{$case->value}")
                                ->label($case->getLabel())
                                ->helperText($case->getDescription())
                                ->live()
                                ->afterStateUpdated(fn () => $this->twitchModsFormAutosave())
                            )
                            ->all()
                    )->columns(4),
                ]),
        ])
            ->statePath('twitchModsFormData');
    }

    public function twitchModsFormAutosave(): void
    {
        $state = $this->twitchModsForm->getRawState();

        $twitchModPermissions = collect(BroadcasterPermission::cases())
            ->filter(fn (BroadcasterPermission $case) => $state["permission_{$case->value}"] ?? false)
            ->values()
            ->all();

        self::getBroadcaster()->update(['twitch_mod_permissions' => $twitchModPermissions]);

    }

    protected function getForms(): array
    {
        return [
            'twitchModsForm',
        ];
    }

    private function makeCreateAction(): CreateAction
    {
        return CreateAction::make()
            ->schema([
                UserSelect::make()
                    ->columnSpanFull()
                    ->rules([
                        Rule::unique('broadcaster_team_members', 'user_id')
                            ->where('broadcaster_id', $this::getBroadcaster()->id),
                        Rule::notIn([$this::getBroadcaster()->id]),
                    ])
                    ->validationMessages([
                        'unique' => __('dashboard/settings/manage-team-member.forms.create.user-select.rules.unique'),
                        'not_in' => __('dashboard/settings/manage-team-member.forms.create.user-select.rules.not_in'),
                    ])
                    ->required(),
                CheckboxList::make('permissions')
                    ->label('dashboard/settings/manage-team-member.table.permissions')
                    ->translateLabel()
                    ->gridDirection(GridDirection::Row)
                    ->columns(2)
                    ->options(BroadcasterPermission::class)
                    ->columnSpanFull(),
            ])
            ->mutateDataUsing(function (array $data): array {
                $data['broadcaster_id'] = $this::getBroadcaster()->id;

                return $data;
            });
    }

    private function makeEditAction(): EditAction
    {
        return EditAction::make()
            ->schema([
                UserSelect::make()
                    ->label('dashboard/settings/manage-team-member.table.user')
                    ->columnSpanFull()
                    ->disabled(),
                CheckboxList::make('permissions')
                    ->label('dashboard/settings/manage-team-member.table.permissions')
                    ->translateLabel()
                    ->gridDirection(GridDirection::Row)
                    ->columns(2)
                    ->options(BroadcasterPermission::class)
                    ->columnSpanFull(),
            ])
            ->mutateDataUsing(function (array $data): array {
                $data['broadcaster_id'] = $this::getBroadcaster()->id;

                return $data;
            });
    }

    private function getBaseQuery(): Builder
    {
        $tenant = $this::getBroadcaster();

        return $tenant->members()->getQuery();
    }
}
