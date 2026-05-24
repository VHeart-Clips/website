<?php

declare(strict_types=1);

namespace App\Filament\Actions\Tables;

use App\Actions\ImportClipAction;
use App\Enums\Clips\ClipStatus;
use App\Enums\FeatureFlag;
use App\Enums\Filament\LucideIcon;
use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Clip\Tag;
use App\Models\User;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\Data\UserDto;
use App\Services\Twitch\TwitchService;
use App\Support\FeatureFlag\Feature;
use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionPipeline;
use App\Support\VHeart\Submissions\Rules\BroadcasterCategorySubmissionRule;
use App\Support\VHeart\Submissions\Rules\BroadcasterConsentSubmissionRule;
use App\Support\VHeart\Submissions\Rules\BroadcasterUserSubmissionRule;
use App\Support\VHeart\Submissions\Rules\MaximumAgeSubmissionRule;
use App\Support\VHeart\Submissions\Rules\MinimumLengthSubmissionRule;
use App\Support\VHeart\Submissions\Rules\SiteCategoryBannedSubmissionRule;
use Closure;
use Deprecated;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Log;

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
                        ->orderBy('order')
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
                    $user = auth()->user();

                    if (! $clipId) {
                        Notification::make()->title(__('clips.errors.clip_not_found'))->danger()->send();

                        $this->halt();
                    }

                    $context = new ClipSubmissionContext($user, $clipId, $twitchService);

                    if (! $context->clip() instanceof ClipDto) {
                        Notification::make()->title(__('clips.errors.clip_not_found'))->danger()->send();

                        $this->halt();
                    }

                    $bypassBroadcasterConsent = Feature::isActive(FeatureFlag::IgnoreBroadcasterConsent)
                        || ($user->can(Permission::BypassConsentCheck) && ($data['broadcaster_consent'] ?? false));

                    $result = ClipSubmissionPipeline::make($twitchService)
                        ->withoutIf(MinimumLengthSubmissionRule::class, $user->can(Permission::BypassMinimumLengthRequirementCheck) && ($data['minimum_length'] ?? false))
                        ->withoutIf(MaximumAgeSubmissionRule::class, $user->can(Permission::BypassMaximumAgeLimitCheck) && ($data['maximum_age'] ?? false))
                        ->withoutIf(SiteCategoryBannedSubmissionRule::class, $user->can(Permission::BypassBannedCategoryCheck) && ($data['category_ban'] ?? false))
                        ->withoutIf([BroadcasterConsentSubmissionRule::class, BroadcasterUserSubmissionRule::class, BroadcasterCategorySubmissionRule::class], $bypassBroadcasterConsent)
                        ->check($context);

                    if (! $result->passed) {
                        Notification::make()
                            ->title($result->message)
                            ->danger()
                            ->send();

                        $this->halt();
                    }

                    $clipInfo = $context->clip();

                    if ($bypassBroadcasterConsent) {
                        User::firstOrCreate([
                            'id' => $clipInfo->broadcasterId,
                        ], [
                            'name' => $clipInfo->broadcasterName,
                        ]);

                        Broadcaster::firstOrCreate([
                            'id' => $clipInfo->broadcasterId,
                        ]);
                    }

                    /** @var UserDto|null $broadcasterDto */
                    [$broadcasterDto] = $twitchService
                        ->asSessionUser()
                        ->getUsers([
                            'id' => [$clipInfo->broadcasterId],
                        ]);

                    if ($broadcasterDto) {
                        User::updateOrCreate([
                            'id' => $broadcasterDto->id,
                        ], [
                            'name' => $broadcasterDto->displayName,
                            'avatar_url' => $broadcasterDto->profileImageUrl,
                        ]);
                    } else {
                        Log::notice('Broadcaster has been removed because they where not found on twitch, possibly banned.', [
                            'broadcaster_id' => $clipInfo->broadcasterId,
                        ]);

                        Broadcaster::find($clipInfo->broadcasterId)?->delete();
                    }

                    User::updateOrCreate([
                        'id' => $clipInfo->creatorId,
                    ], [
                        'name' => $clipInfo->creatorName,
                    ]);

                    $clip = $importClipAction->execute($clipInfo, $user, $data['tags']);

                    if ($bypassBroadcasterConsent) {
                        $broadcasterConsentExists = Broadcaster::query()
                            ->where('id', $clipInfo->broadcasterId)
                            ->whereJsonLength('consent', '>', '0')
                            ->exists();

                        if (! $broadcasterConsentExists) {
                            $clip->update([
                                'status' => ClipStatus::NeedApproval,
                            ]);
                        }
                    }

                    Notification::make()
                        ->title(__('clips.flash.submitted'))
                        ->success()
                        ->send();

                } catch (Halt $e) {
                    throw $e;
                } catch (Exception $e) {
                    report($e);

                    Notification::make()
                        ->title(__('admin/resources/clips.notifications.submit_error.title'))
                        ->body(__('admin/resources/clips.notifications.submit_error.body'))
                        ->danger()
                        ->send();

                    $this->halt(true);
                }
            });
    }

    /**
     * Allows the user to bypass selected restrictions if they have permissions for them.
     */
    #[Deprecated(message: 'Do not use outside Team panel.')]
    public function withBypass(bool $state = true): static
    {
        $this->bypassable = $state;

        return $this;
    }
}
