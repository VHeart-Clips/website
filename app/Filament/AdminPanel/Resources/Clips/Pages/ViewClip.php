<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Pages;

use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Clips\Actions\Management\AttachToCompilationAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Management\ClipFeedbackAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Moderation\FlagClipAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Moderation\UnflagClipAction;
use App\Filament\AdminPanel\Resources\Clips\ClipResource;
use App\Filament\Resources\Clips\ClipActions;
use App\Jobs\ClipStatsUpdateJob;
use App\Models\Clip;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

class ViewClip extends ViewRecord
{
    protected static string $resource = ClipResource::class;

    private ?bool $ignoredCache = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_twitch')
                ->label(__('admin/resources/clips.actions.view_on_twitch'))
                ->icon(LucideIcon::ExternalLink)
                ->url(fn (Clip $clip): string => $clip->getClipUrl())
                ->openUrlInNewTab(),

            ActionGroup::make([
                AttachToCompilationAction::make(),
                ClipFeedbackAction::make(),
                $this->makeDispatchClipStatsUpdateAction(),
            ])
                ->label('Management')
                ->button(),

            ActionGroup::make([
                FlagClipAction::make(),
                UnflagClipAction::make(),
            ])
                ->label('Moderation')
                ->button(),

            CommentsAction::make()
                ->mentionables(fn (Model $record) => User::query()->whereHas('roles')->get())
                ->authorize('comment')
                ->perPage(4)
                ->loadMoreIncrementsBy(8)
                ->modalWidth(Width::SevenExtraLarge),

            EditAction::make(),

            $this->makeIgnoreClipAction(),
            ClipActions::reportableActionGroup(),
        ];
    }

    private function makeIgnoreClipAction(): Action
    {
        return Action::make('toggleIgnore')
            ->iconButton()
            ->rateLimit(2)
            ->icon(fn (): LucideIcon => $this->resolveIgnored() ? LucideIcon::EyeDashed : LucideIcon::Eye)
            ->tooltip(fn (): string => $this->resolveIgnored() ? 'Unignore' : 'Ignore')
            ->successNotificationTitle(fn (): string => ! $this->resolveIgnored() ? 'Clip has been Unignored' : 'Clip has been Ignored')
            ->action(function (Action $action, Clip $clip): void {
                $clip->ignoredByUsers()->toggle(auth()->id());

                $this->ignoredCache = null;
            });
    }

    private function resolveIgnored(): bool
    {
        /** @var Clip $clip */
        $clip = $this->getRecord();

        return $this->ignoredCache ??= $clip
            ->ignoredByUsers()
            ->whereKey(auth()->id())
            ->exists();
    }

    private function makeDispatchClipStatsUpdateAction(): Action
    {
        return Action::make('dispatchClipStatsUpdate')
            ->label('Schedule Stats Update')
            ->authorize('superadmin')
            ->icon(LucideIcon::RefreshCcw)
            ->color('info')
            ->requiresConfirmation()
            ->action(function (Clip $clip): void {
                dispatch(new ClipStatsUpdateJob($clip->id));

                Notification::make()
                    ->title('Clip has been Scheduled for Stats update.')
                    ->success()
                    ->send();
            });
    }
}
