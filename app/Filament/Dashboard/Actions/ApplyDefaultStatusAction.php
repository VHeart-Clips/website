<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Actions;

use App\Enums\Clips\ClipStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

/**
 * This action will update all clips with the provided statuses to the current users/broadcasters default status
 *
 * We will skip Clips that are already in a locked Compilation though, mainly for consistency's sake.
 *
 * we also rate limit the user to prevent them spamming this action since its kinda expensive
 */
class ApplyDefaultStatusAction extends Action
{
    private const int RATE_LIMIT_MAX_ATTEMPTS = 3;

    private const int RATE_LIMIT_DECAY_SECONDS = 300;

    private const int BULK_CHUNK_SIZE = 100;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('dashboard/settings/manage-general-settings.actions.apply_default_status.label'))
            ->modalHeading(__('dashboard/settings/manage-general-settings.actions.apply_default_status.modal.heading'))
            ->modalDescription(fn (): string => __('dashboard/settings/manage-general-settings.actions.apply_default_status.modal.description', [
                'status' => $this->resolveDefaultClipStatus()->getLabel(),
            ]))
            ->modalSubmitActionLabel(__('dashboard/settings/manage-general-settings.actions.apply_default_status.modal.submit'))
            ->icon(LucideIcon::RefreshCcw)
            ->color('warning')
            ->modalIcon(LucideIcon::TriangleAlert)
            ->modalWidth(Width::Prose)
            ->disabled(fn (): bool => ! $this->resolveDefaultClipStatus()
                || RateLimiter::tooManyAttempts($this->getRateLimitKey(), self::RATE_LIMIT_MAX_ATTEMPTS)
                || $this->clipsEligibleForReset()->exists() === false
            )
            ->tooltip(fn (): ?string => RateLimiter::tooManyAttempts($this->getRateLimitKey(), self::RATE_LIMIT_MAX_ATTEMPTS)
                ? __('dashboard/settings/manage-general-settings.actions.apply_default_status.notifications.rate_limited.title')
                : null
            )
            ->schema([
                CheckboxList::make('statuses')
                    ->hiddenLabel()
                    ->helperText(__('dashboard/settings/manage-general-settings.actions.apply_default_status.modal.helper_text'))
                    ->options(fn (): array => $this->clipsEligibleForReset()
                        ->selectRaw('status, COUNT(*) as count')
                        ->groupBy('status')
                        ->get()
                        ->mapWithKeys(fn (Clip $clip): array => [
                            $clip->status->value => "{$clip->status->getLabel()} ({$clip->getRawOriginal('count')})",
                        ])
                        ->all()
                    )
                    ->required(),
            ])
            ->before(function (): void {
                if (RateLimiter::tooManyAttempts($this->getRateLimitKey(), self::RATE_LIMIT_MAX_ATTEMPTS)) {
                    $seconds = RateLimiter::availableIn($this->getRateLimitKey());

                    $this->notifyRateLimited($seconds);
                    $this->halt();
                }

                RateLimiter::hit($this->getRateLimitKey(), self::RATE_LIMIT_DECAY_SECONDS);
            })
            ->action(function (array $data): void {
                $currentDefault = $this->resolveDefaultClipStatus();

                if ($currentDefault === ClipStatus::Unknown) {
                    $this->halt();
                }

                $selected = array_filter(
                    $data['statuses'],
                    static fn (int $status): bool => ClipStatus::tryFrom($status) !== $currentDefault,
                );

                if ($selected === []) {
                    $this->halt();
                }

                $count = $this->applyDefaultStatus($selected, $currentDefault);

                $count === 0
                    ? $this->notifyNoneMatched()
                    : $this->notifySuccess($count);
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'applyDefaultStatus';
    }

    private function resolveDefaultClipStatus(): ?ClipStatus
    {
        return auth()->user()?->broadcaster?->default_clip_status;
    }

    private function clipsEligibleForReset(): Builder
    {
        return auth()->user()
            ->broadcastedClips()
            ->whereDoesntHave('compilations', fn (Builder $query) => $query
                ->whereNotIn('status', CompilationStatus::getVoteDisabledCases())
            )
            ->withoutGlobalScopes([ClipPermissionScope::class, ClipWithoutBannedCategoryScope::class])
            ->whereNot('status', $this->resolveDefaultClipStatus());
    }

    private function applyDefaultStatus(array $selected, ClipStatus $currentDefault): int
    {
        $count = 0;

        DB::transaction(static function () use ($selected, $currentDefault, &$count): void {
            auth()->user()
                ->broadcastedClips()
                ->withoutGlobalScopes([ClipPermissionScope::class, ClipWithoutBannedCategoryScope::class])
                ->whereDoesntHave('compilations', fn (Builder $query) => $query
                    ->whereNotIn('status', CompilationStatus::getVoteDisabledCases())
                )
                ->whereIn('status', $selected)
                ->lockForUpdate()
                ->lazyById(self::BULK_CHUNK_SIZE)
                ->each(function (Clip $clip) use ($currentDefault, &$count): void {
                    $clip->update(['status' => $currentDefault]);
                    $count++;
                });
        });

        return $count;
    }

    private function notifyNoneMatched(): void
    {
        Notification::make()
            ->title(__('dashboard/settings/manage-general-settings.actions.apply_default_status.notifications.none_matched'))
            ->warning()
            ->send();
    }

    private function notifySuccess(int $count): void
    {
        Notification::make()
            ->title(trans_choice(
                'dashboard/settings/manage-general-settings.actions.apply_default_status.notifications.success.title',
                $count,
                ['count' => $count],
            ))
            ->body(trans_choice(
                key: 'dashboard/settings/manage-general-settings.actions.apply_default_status.notifications.success.body',
                number: $count,
                replace: [
                    'count' => $count,
                    'status' => $this->resolveDefaultClipStatus()->getLabel(),
                ],
            ))
            ->success()
            ->send();
    }

    private function notifyRateLimited(int $seconds): void
    {
        Notification::make(self::class.':rate-limit')
            ->title(__('dashboard/settings/manage-general-settings.actions.apply_default_status.notifications.rate_limited.title'))
            ->body(trans_choice(
                key: 'dashboard/settings/manage-general-settings.actions.apply_default_status.notifications.rate_limited.body',
                number: $seconds,
                replace: [
                    'seconds' => $seconds,
                ]))
            ->warning()
            ->send();
    }

    private function getRateLimitKey(): string
    {
        return self::class.':'.auth()->id();
    }
}
