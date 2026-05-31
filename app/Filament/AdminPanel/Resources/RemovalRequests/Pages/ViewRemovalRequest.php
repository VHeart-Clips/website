<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\RemovalRequests\Pages;

use App\Enums\Broadcaster\RemovalRequestStatus;
use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\RemovalRequests\RemovalRequestResource;
use App\Models\Broadcaster\RemovalRequest;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewRemovalRequest extends ViewRecord
{
    protected static string $resource = RemovalRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RemovalRequestResource::resourceLinkActionGroup(),

            $this->makeClaimAction(),
            $this->makeUnclaimAction(),

            ActionGroup::make([
                $this->makeResolveAction(RemovalRequestStatus::Approved)
                    ->icon(RemovalRequestStatus::Approved->getIcon())
                    ->label(RemovalRequestStatus::Approved->getLabel())
                    ->color(RemovalRequestStatus::Approved->getColor()),

                $this->makeResolveAction(RemovalRequestStatus::Rejected)
                    ->icon(RemovalRequestStatus::Rejected->getIcon())
                    ->label(RemovalRequestStatus::Rejected->getLabel())
                    ->color(RemovalRequestStatus::Rejected->getColor()),
            ])
                ->label('admin/resources/removal-requests.actions.resolve-request-group.label')
                ->translateLabel()
                ->color('info')
                ->icon(LucideIcon::Info)
                ->button(),

            ActionGroup::make([
                $this->makeForceClaimAction(),
                $this->makeResetRequestAction(),
            ])->color('danger'),
        ];
    }

    private static function notifyClaimed(): void
    {
        Notification::make('claimed')
            ->title(__('admin/resources/removal-requests.notifications.claimed.title'))
            ->success()
            ->send();
    }

    private function makeClaimAction(): Action
    {
        return Action::make('claim')
            ->label('admin/resources/removal-requests.actions.claim.label')
            ->translateLabel()
            ->requiresConfirmation()
            ->authorize('adminClaimAction')
            ->action(function (RemovalRequest $record) {
                DB::transaction(static function () use (&$record) {
                    $removalRequest = RemovalRequest::query()
                        ->lockForUpdate()
                        ->find($record->id);

                    if (! $removalRequest) {
                        return;
                    }

                    if ($removalRequest->claimed_by) {
                        Notification::make('already-claimed')
                            ->title(__('admin/resources/removal-requests.notifications.already-claimed.title'))
                            ->body(__('admin/resources/removal-requests.notifications.already-claimed.body', [
                                'name' => $removalRequest->claimer->name,
                                'ago' => $removalRequest->claimed_at->diffForHumans(now()),
                            ]))
                            ->warning()
                            ->send();

                        return;
                    }

                    $removalRequest->update(['claimed_by' => auth()->id(), 'claimed_at' => now()]);

                    self::notifyClaimed();
                });

                $record->refresh();
            })
            ->color('info');
    }

    private function makeForceClaimAction(): Action
    {
        return Action::make('forceClaim')
            ->label('admin/resources/removal-requests.actions.force-claim.label')
            ->translateLabel()
            ->requiresConfirmation()
            ->authorize('adminForceClaimAction')
            ->action(function (RemovalRequest $record) {
                DB::transaction(static function () use (&$record) {
                    $removalRequest = RemovalRequest::query()
                        ->lockForUpdate()
                        ->find($record->id);

                    if (! $removalRequest) {
                        return;
                    }

                    if ($removalRequest->claimed_by) {
                        Notification::make('force-claimed')
                            ->title(__('admin/resources/removal-requests.notifications.force-claimed.title'))
                            ->body(__('admin/resources/removal-requests.notifications.force-claimed.body', [
                                'name' => $removalRequest->claimer->name,
                                'ago' => $removalRequest->claimed_at->diffForHumans(now()),
                            ]))
                            ->warning()
                            ->send();
                    } else {
                        self::notifyClaimed();
                    }

                    $removalRequest->update(['claimed_by' => auth()->id(), 'claimed_at' => now()]);
                });

                $record->refresh();
            })
            ->icon(LucideIcon::RefreshCcw)
            ->color('danger');
    }

    private function makeUnclaimAction(): Action
    {
        return Action::make('unclaim')
            ->label('admin/resources/removal-requests.actions.unclaim.label')
            ->translateLabel()
            ->requiresConfirmation()
            ->authorize('adminUnclaimAction')
            ->action(function (RemovalRequest $record) {
                Notification::make('unclaimed')
                    ->title(__('admin/resources/removal-requests.notifications.unclaimed.title'))
                    ->success()
                    ->send();

                $record->update(['claimed_by' => null, 'claimed_at' => null]);
            })
            ->color('warning');
    }

    private function makeResetRequestAction(): Action
    {
        return Action::make('resetRequestStatus')
            ->label('admin/resources/removal-requests.actions.reset-request.label')
            ->translateLabel()
            ->requiresConfirmation()
            ->authorize('superadmin')
            ->visible(fn (RemovalRequest $record): bool => $record->resolved_by || $record->resolved_at || $record->claimed_by || $record->claimed_at || $record->status !== RemovalRequestStatus::Pending)
            ->action(function (RemovalRequest $record) {
                DB::transaction(static function () use (&$record) {
                    $removalRequest = RemovalRequest::query()
                        ->lockForUpdate()
                        ->find($record->id);

                    if (! $removalRequest) {
                        return;
                    }

                    Notification::make('reset-request')
                        ->title(__('admin/resources/removal-requests.notifications.reset-request.title'))
                        ->warning()
                        ->send();

                    $removalRequest->update([
                        'claimed_by' => null,
                        'claimed_at' => null,
                        'resolved_by' => null,
                        'resolved_at' => null,
                        'status' => RemovalRequestStatus::Pending,
                    ]);
                });

                $record->refresh();
            })
            ->icon(LucideIcon::TriangleAlert)
            ->color('danger');
    }

    private function makeResolveAction(RemovalRequestStatus $requestStatus): Action
    {
        return Action::make('resolveRequest'.$requestStatus->name)
            ->requiresConfirmation()
            ->authorize('update')
            ->visible(fn (RemovalRequest $record): bool => $record->claimed_by === auth()->id() && $record->status === RemovalRequestStatus::Pending)
            ->action(function (RemovalRequest $record) use ($requestStatus) {
                Notification::make('resolved')
                    ->title(__('admin/resources/removal-requests.notifications.resolved.title', [
                        'status' => $requestStatus->getLabel(),
                    ]))
                    ->success()
                    ->send();

                $record->update([
                    'status' => $requestStatus,
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);
            });
    }
}
