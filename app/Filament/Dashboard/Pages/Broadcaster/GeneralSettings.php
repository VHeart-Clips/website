<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Pages\Broadcaster;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Broadcaster\DashboardNavigationGroup;
use App\Enums\Broadcaster\DashboardNavigationItem;
use App\Enums\Clips\ClipStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\Broadcaster\Broadcaster;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * @property-read Schema $consentForm
 * @property-read Schema $defaultClipStatusForm
 */
class GeneralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed>|null */
    public ?array $consentFormData = [];

    public ?array $defaultClipStatusFormData = [];

    public ?array $submissionsSettingFormData = [];

    protected static string|null|BackedEnum $navigationIcon = LucideIcon::Settings;

    protected static ?int $navigationSort = 999;

    protected static string|null|UnitEnum $navigationGroup = DashboardNavigationGroup::Settings;

    protected string $view = 'filament.dashboard.pages.broadcaster.manage-general-settings';

    protected ?string $heading = '';

    public static function getNavigationLabel(): string
    {
        return DashboardNavigationItem::GeneralSettings->getLabel();
    }

    public static function canAccess(): bool
    {
        // later we can check for permission to this specific page here
        return Filament::getTenant()?->id === auth()->user()?->id;
    }

    public function getTitle(): string|Htmlable
    {
        return Filament::getTenant()->name.' - '.DashboardNavigationItem::GeneralSettings->getLabel();
    }

    public function mount(): void
    {
        $this->consentForm->fill(
            collect(BroadcasterConsent::cases())
                ->mapWithKeys(fn (BroadcasterConsent $case): array => [
                    "consent_{$case->value}" => $this->getRecord()->consent?->contains(fn (BroadcasterConsent $c): bool => $c === $case) ?? false,
                ])
                ->all()
        );
        $this->defaultClipStatusForm->fill($this->getRecord()->only(['default_clip_status']));
        $this->submissionsSettingForm->fill($this->getRecord()->only(['submit_user_allowed', 'submit_vip_allowed', 'submit_mods_allowed']));
    }

    public function defaultClipStatusForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('default_clip_status')
                ->heading(__('dashboard/settings/manage-general-settings.sections.default_clip_status.label'))
                ->description(__('dashboard/settings/manage-general-settings.sections.default_clip_status.description'))
                ->schema([Form::make([
                    Radio::make('default_clip_status')
                        ->hiddenLabel()
                        ->options(
                            collect(ClipStatus::defaultableOptions())
                                ->mapWithKeys(fn (ClipStatus $status): array => [$status->value => $status->getLabel()])
                                ->toArray()
                        )
                        ->descriptions(
                            collect(ClipStatus::defaultableOptions())
                                ->mapWithKeys(fn (ClipStatus $status): array => [$status->value => __('onboarding.setup.default_clip_status.options.'.Str::snake($status->name))])
                                ->toArray()
                        ),
                ])
                    ->live()
                    ->afterStateUpdated(fn () => $this->defaultClipStatusFormAutosave()),
                ]),
        ])->statePath('defaultClipStatusFormData');
    }

    public function defaultClipStatusFormAutosave(): void
    {
        $state = $this->defaultClipStatusForm->getState();
        $this->getRecord()->update($state);
        $this->getRecord()->refresh();
        $this->mount();
    }

    public function consentForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('consent')
                ->heading(__('dashboard/settings/manage-general-settings.sections.consent.label'))
                ->description(__('dashboard/settings/manage-general-settings.sections.consent.description'))
                ->schema([Form::make(
                    collect(BroadcasterConsent::cases())
                        ->map(fn (BroadcasterConsent $case): Toggle => Toggle::make("consent_{$case->value}")
                            ->label($case->getLabel())
                            ->live()
                            ->afterStateUpdated(fn () => $this->consentFormAutosave())
                        )
                        ->all()
                ),
                ]),
        ])
            ->statePath('consentFormData');
    }

    public function consentFormAutosave(): void
    {
        $state = $this->consentForm->getRawState();

        $consent = collect(BroadcasterConsent::cases())
            ->filter(fn (BroadcasterConsent $case) => $state["consent_{$case->value}"] ?? false)
            ->values()
            ->all();

        $this->getRecord()->update(['consent' => $consent]);
    }

    public function submissionsSettingForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('submissions_settings')
                ->heading(__('dashboard/settings/manage-general-settings.sections.submissions_settings.label'))
                ->description(__('dashboard/settings/manage-general-settings.sections.submissions_settings.description'))
                ->schema([Form::make([
                    Toggle::make('submit_user_allowed')
                        ->label('dashboard/settings/manage-general-settings.sections.submissions_settings.form.submit_user_allowed.label')
                        ->helperText(__('dashboard/settings/manage-general-settings.sections.submissions_settings.form.submit_user_allowed.description'))
                        ->translateLabel()
                        ->afterStateUpdated(function (bool $state, Set $set): void {
                            if ($state) {
                                $set('submit_vip_allowed', true);
                                $set('submit_mods_allowed', true);
                            }
                            $this->submissionsSettingFormAutosave();
                        }),
                    Toggle::make('submit_vip_allowed')
                        ->label('dashboard/settings/manage-general-settings.sections.submissions_settings.form.submit_vip_allowed.label')
                        ->helperText(__('dashboard/settings/manage-general-settings.sections.submissions_settings.form.submit_vip_allowed.description'))
                        ->translateLabel()
                        ->afterStateUpdated(function (bool $state, Set $set): void {
                            if (! $state) {
                                $set('submit_user_allowed', false);
                            }
                            $this->submissionsSettingFormAutosave();
                        }),
                    Toggle::make('submit_mods_allowed')
                        ->label('dashboard/settings/manage-general-settings.sections.submissions_settings.form.submit_mods_allowed.label')
                        ->helperText(__('dashboard/settings/manage-general-settings.sections.submissions_settings.form.submit_mods_allowed.description'))
                        ->translateLabel()
                        ->afterStateUpdated(function (bool $state, Set $set): void {
                            if (! $state) {
                                $set('submit_user_allowed', false);
                            }
                            $this->submissionsSettingFormAutosave();
                        }),
                ])
                    ->live(),
                ]),
        ])->statePath('submissionsSettingFormData');
    }

    public function submissionsSettingFormAutosave(): void
    {
        $state = $this->submissionsSettingForm->getState();

        if ($state['submit_user_allowed'] === true) {
            $state['submit_vip_allowed'] = true;
            $state['submit_mods_allowed'] = true;
        }

        $this->getRecord()->update($state);
        $this->getRecord()->refresh();
        $this->mount();

    }

    /**
     * @return Broadcaster
     */
    public function getRecord(): Model
    {
        return Filament::getTenant();
    }

    protected function getForms(): array
    {
        return [
            'consentForm',
            'defaultClipStatusForm',
            'submissionsSettingForm',
        ];
    }
}
