<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Pages;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Clips\ClipStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Broadcaster\BroadcasterConsentLog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Onboarding extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    protected string $view = 'filament.dashboard.pages.onboarding-page';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::UserPlus;

    public static function canAccess(): bool
    {
        return Filament::getTenant()?->getKey() === auth()->id();
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('onboarding.heading', ['username' => auth()->user()->name]);
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('onboarding.setup.heading');
    }

    public function mount(): void
    {
        $broadcaster = $this->getBroadcaster();

        if ($broadcaster->exists && $broadcaster->onboarded_at !== null) {
            $this->redirect(Dashboard::getUrl(panel: 'dashboard', tenant: $broadcaster));
        }

        $this->getSchema('form')->fill([
            'submit_user_allowed' => true,
            'submit_mods_allowed' => true,
            'default_clip_status' => ClipStatus::NeedApproval,
            'consent' => [],
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label(__('onboarding.setup.submit'))
                            ->keyBindings(['mod+s'])
                            ->submit('save'),
                    ])
                        ->alignment(Alignment::End)
                        ->sticky()
                        ->key('form-actions'),
                ])
                ->livewireSubmitHandler('save')
                ->id('form'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('formData')
            ->schema([
                Section::make(__('onboarding.setup.consent.heading'))
                    ->description(__('onboarding.setup.consent.subheading'))
                    ->schema([
                        CheckboxList::make('consent')
                            ->options(BroadcasterConsent::class)
                            ->hiddenLabel(),
                    ]),

                Section::make(__('onboarding.setup.default_clip_status.heading'))
                    ->description(__('onboarding.setup.default_clip_status.subheading'))
                    ->schema([
                        Radio::make('default_clip_status')
                            ->options(
                                collect(ClipStatus::defaultableOptions())
                                    ->mapWithKeys(fn (ClipStatus $status): array => [$status->value => $status->getLabel()])
                                    ->toArray()
                            )
                            ->descriptions(
                                collect(ClipStatus::defaultableOptions())
                                    ->mapWithKeys(fn (ClipStatus $status): array => [
                                        $status->value => __('onboarding.setup.default_clip_status.options.'.Str::snake($status->name)),
                                    ])
                                    ->toArray()
                            )
                            ->default(ClipStatus::NeedApproval)
                            ->hiddenLabel()
                            ->required(),
                    ]),

                Section::make(__('onboarding.setup.submissions.heading'))
                    ->description(__('onboarding.setup.submissions.subheading'))
                    ->schema([
                        Toggle::make('submit_user_allowed')
                            ->afterStateUpdated(function (bool $state, Set $set): void {
                                if ($state) {
                                    $set('submit_mods_allowed', true);
                                }
                            })
                            ->extraAlpineAttributes(['x-on:change' => 'console.log(1)'])
                            ->helperText(__('onboarding.setup.submissions.options.everyone.description'))
                            ->label(__('onboarding.setup.submissions.options.everyone.label'))
                            ->default(true)
                            ->live(),

                        Toggle::make('submit_mods_allowed')
                            ->afterStateUpdated(function (bool $state, Set $set): void {
                                if (! $state) {
                                    $set('submit_user_allowed', false);
                                }
                            })
                            ->helperText(__('onboarding.setup.submissions.options.mods.description'))
                            ->label(__('onboarding.setup.submissions.options.mods.label'))
                            ->default(true)
                            ->live(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->getSchema('form')->getState();
        $broadcaster = $this->getBroadcaster();

        DB::transaction(function () use ($broadcaster, $state): void {
            Broadcaster::withTrashed()->updateOrCreate(['id' => $broadcaster->id], [
                'consent' => $state['consent'],
                'submit_user_allowed' => $state['submit_user_allowed'],
                'submit_mods_allowed' => $state['submit_mods_allowed'],
                'default_clip_status' => $state['default_clip_status'],
                'onboarded_at' => now(),
                'deleted_at' => null,
            ]);

            BroadcasterConsentLog::create([
                'broadcaster_id' => $broadcaster->id,
                'state' => collect($state['consent'])->values()->all(),
                'changed_by' => auth()->id(),
                'change_reason' => 'Self Onboarding',
                'changed_at' => now(),
            ]);
        });

        $this->redirect(Dashboard::getUrl(panel: 'dashboard', tenant: $broadcaster));
    }

    /**
     * just a wrapper around getTenant so we have autocompletion stuff for Broadcaster
     *
     * @return Broadcaster
     */
    private function getBroadcaster(): Model
    {
        return Filament::getTenant();
    }
}
