<?php

declare(strict_types=1);

namespace App\Filament\Actions\Tables;

use App\Actions\ImportClipAction;
use App\Enums\FeatureFlag;
use App\Enums\Filament\LucideIcon;
use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Category;
use App\Models\Clip;
use App\Models\Clip\Tag;
use App\Models\User;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\Enums\TwitchEndpoints;
use App\Services\Twitch\Exceptions\TwitchApiException;
use App\Services\Twitch\TwitchService;
use App\Support\FeatureFlag\Feature;
use Carbon\CarbonInterval;
use Closure;
use Deprecated;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;

class SubmitClipAction extends Action
{
    protected bool $bypassable = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('submit_clip')
            ->label('filament/actions/tables.clips.submit_action.label')
            ->translateLabel()
            ->icon(LucideIcon::Plus)
            ->schema([
                TextInput::make('uri')
                    ->label('filament/actions/tables.clips.submit_action.form.uri')
                    ->translateLabel()
                    ->placeholder('https://clips.twitch.tv/...')
                    ->rules([
                        fn (TwitchService $twitchService): Closure => static function (string $attribute, $value, Closure $fail) use ($twitchService): void {
                            if (! $twitchService->parseClipId($value)) {
                                $fail(__('clips.errors.clip_url_required'));
                            }
                        },
                    ])
                    ->required(),

                Select::make('tags')
                    ->label('filament/actions/tables.clips.submit_action.form.tags')
                    ->translateLabel()
                    ->multiple()
                    ->minItems(1)
                    ->maxItems(3)
                    ->options(fn () => Tag::query()
                        ->whereLocale('name', app()->getLocale())
                        ->pluck('name', 'id')
                    )
                    ->required(),

                Section::make(__('filament/actions/tables.clips.submit_action.form.bypass.label'))
                    ->description(__('filament/actions/tables.clips.submit_action.form.bypass.description'))
                    ->compact()
                    ->hidden(function (): bool {
                        if (! $this->bypassable) {
                            return true;
                        }

                        return ! auth()->user()?->canAny([
                            Permission::BypassConsentCheck,
                            Permission::BypassMaximumAgeLimitCheck,
                            Permission::BypassBannedCategoryCheck,
                            Permission::BypassMinimumLengthRequirementCheck,
                        ]);
                    })
                    ->collapsed()
                    ->schema([
                        Toggle::make('broadcaster_consent')
                            ->hidden(fn (): bool => ! app()->isLocal())
                            ->label('filament/actions/tables.clips.submit_action.form.bypass.options.broadcaster_consent')
                            ->translateLabel()
                            ->onColor('danger')
                            ->disabled(fn (): bool => Feature::isActive(FeatureFlag::IgnoreBroadcasterConsent))
                            ->default(fn (): bool => Feature::isActive(FeatureFlag::IgnoreBroadcasterConsent)),
                        Toggle::make('category_ban')
                            ->hidden(fn (): bool => ! auth()->user()?->can(Permission::BypassBannedCategoryCheck))
                            ->label('filament/actions/tables.clips.submit_action.form.bypass.options.category_ban')
                            ->translateLabel()
                            ->default(false),
                        Toggle::make('maximum_age')
                            ->hidden(fn (): bool => ! auth()->user()?->can(Permission::BypassMaximumAgeLimitCheck))
                            ->label('filament/actions/tables.clips.submit_action.form.bypass.options.maximum_age')
                            ->translateLabel()
                            ->default(false),
                        Toggle::make('minimum_length')
                            ->hidden(fn (): bool => ! auth()->user()?->can(Permission::BypassMinimumLengthRequirementCheck))
                            ->label('filament/actions/tables.clips.submit_action.form.bypass.options.minimum_length')
                            ->translateLabel()
                            ->default(false),
                    ])->columns(2),
            ])
            ->action(function (array $data, ImportClipAction $importClipAction, TwitchService $twitchService): void {
                try {
                    $clipId = $twitchService->parseClipId($data['uri']);
                    if (! $clipId) {
                        Notification::make()->title(__('clips.errors.clip_not_found'))->danger()->send();

                        return;
                    }

                    $user = auth()->user();
                    $clipInfo = $twitchService
                        ->asSessionUser()
                        ->getClip($clipId);

                    if (! $clipInfo instanceof ClipDto) {
                        Notification::make()->title(__('clips.errors.clip_not_found'))->danger()->send();

                        return;
                    }

                    $bypassBroadcasterConsent = Feature::isActive(FeatureFlag::IgnoreBroadcasterConsent) || (auth()->user()?->can(Permission::BypassConsentCheck) && $data['broadcaster_consent']);
                    $bypassMinLength = auth()->user()?->can(Permission::BypassBannedCategoryCheck) && $data['minimum_length'];
                    $bypassMaxAge = auth()->user()?->can(Permission::BypassMaximumAgeLimitCheck) && $data['maximum_age'];
                    $bypassCategoryBan = auth()->user()?->can(Permission::BypassBannedCategoryCheck) && $data['category_ban'];

                    if (Clip::query()->where('twitch_id', $clipInfo->id)->exists()) {
                        Notification::make()->title(__('clips.errors.clip_already_known'))->danger()->send();

                        return;
                    }

                    /** @var int $minClipDuration */
                    $minClipDuration = config('vheart.clips.submission.minimum_length', 5);
                    if (! $bypassMinLength && $clipInfo->duration < $minClipDuration) {
                        Notification::make()->title(__('clips.errors.too_short', [
                            'seconds' => $minClipDuration,
                        ]))->danger()->send();

                        return;
                    }

                    /** @var CarbonInterval $maxClipAge */
                    $maxClipAge = config('vheart.clips.submission.maximum_age');
                    if (! $bypassMaxAge && $maxClipAge && $clipInfo->createdAt->add($maxClipAge)->isPast()) {
                        Notification::make()->title(__('clips.errors.too_old', [
                            'age' => $maxClipAge->forHumans(),
                        ]))->danger()->send();

                        return;
                    }

                    // Check Site Category Ban
                    if (! $bypassCategoryBan) {
                        $isCategoryBanned = Category::query()
                            ->where('is_banned', true)
                            ->where('id', $clipInfo->gameId)
                            ->exists();

                        if ($isCategoryBanned) {
                            Notification::make()->title(__('clips.errors.category_blocked'))->danger()->send();

                            return;
                        }
                    }

                    // Broadcaster
                    if (! $bypassBroadcasterConsent) {
                        $broadcaster = Broadcaster::query()
                            ->where('id', $clipInfo->broadcasterId)
                            ->whereGaveConsent()
                            ->with(['filters'])
                            ->first();

                        if (! $broadcaster) {
                            Notification::make()->title(__('clips.errors.broadcaster_not_allowed'))->danger()->send();

                            return;
                        }

                        $userType = $user->getMorphClass();
                        $categoryType = new Category()->getMorphClass();

                        $groupedFilters = $broadcaster->filters->groupBy(['filterable_type', 'state']);
                        $allowedUsers = $groupedFilters->get($userType)?->get(true)?->pluck('filterable_id')->toArray() ?? [];
                        $disallowedUsers = $groupedFilters->get($userType)?->get(false)?->pluck('filterable_id')->toArray() ?? [];
                        $allowedCategories = $groupedFilters->get($categoryType)?->get(true)?->pluck('filterable_id')->toArray() ?? [];
                        $disallowedCategories = $groupedFilters->get($categoryType)?->get(false)?->pluck('filterable_id')->toArray() ?? [];

                        if (! $this->passesUserChecks($user, $broadcaster, $disallowedUsers, $allowedUsers, $twitchService)) {
                            Notification::make()->title(__('clips.errors.user_not_allowed_for_broadcaster'))->danger()->send();

                            return;
                        }

                        if (! $this->passesCategoryChecks($clipInfo, $disallowedCategories, $allowedCategories)) {
                            Notification::make()->title(__('clips.errors.category_blocked'))->danger()->send();

                            return;
                        }
                    } else {
                        Broadcaster::firstOrCreate([
                            'id' => $clipInfo->broadcasterId,
                        ]);
                    }

                    User::updateOrCreate([
                        'id' => $clipInfo->creatorId,
                    ], [
                        'name' => $clipInfo->creatorName,
                    ]);

                    $importClipAction->execute($clipInfo, $user, $data['tags']);

                    Notification::make()
                        ->title(__('clips.flash.submitted'))
                        ->success()
                        ->send();

                } catch (Exception $e) {
                    Notification::make()
                        ->title(__('admin/resources/clips.notifications.submit_error'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Allows the user to bypass selected restrictions if they have permissions for them.
     */
    #[Deprecated('Do not use outside Team panel.')]
    public function withBypass(bool $state = true): static
    {
        $this->bypassable = $state;

        return $this;
    }

    protected function passesUserChecks(User $user, Broadcaster $broadcaster, array $disallowedUsers, array $allowedUsers, TwitchService $twitchService): bool
    {
        if (in_array($user->id, $disallowedUsers, true)) {
            return false;
        }

        $isAllowed = $broadcaster->submit_user_allowed || $broadcaster->id === $user->id;

        if ($isAllowed) {
            return true;
        }

        if ($allowedUsers !== []) {
            $isAllowed = in_array($user->id, $allowedUsers, true);
        }

        if (! $isAllowed && $broadcaster->submit_mods_allowed) {
            $isAllowed = $twitchService
                ->asSessionUser()
                ->isModeratorFor($broadcaster->user);
        }

        if (! $isAllowed && $broadcaster->submit_vip_allowed) {
            try {
                $vipInfos = $twitchService
                    ->asUser($broadcaster->user)
                    ->get(TwitchEndpoints::GetVIPs, [
                        'user_id' => $user->id,
                        'broadcaster_id' => $broadcaster->id,
                    ]);
                $isAllowed = ! empty($vipInfos['data']);
            } catch (TwitchApiException $th) {
                report($th);

                return false;
            }
        }

        return $isAllowed;
    }

    protected function passesCategoryChecks(ClipDto $clipInfo, array $disallowedCategories, array $allowedCategories): bool
    {
        $gameId = $clipInfo->gameId;

        if (in_array($gameId, $disallowedCategories, true)) {
            return false;
        }

        if ($allowedCategories && count($allowedCategories) > 0) {
            return in_array($gameId, $allowedCategories, true);
        }

        return true;
    }
}
