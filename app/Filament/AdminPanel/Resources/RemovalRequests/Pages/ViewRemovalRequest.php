<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\RemovalRequests\Pages;

use App\Enums\Broadcaster\RemovalRequestStatus;
use App\Enums\Clips\ClipStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\RemovalRequests\RemovalRequestResource;
use App\Models\Broadcaster\RemovalRequest;
use App\Models\Clip\Compilation;
use App\Models\Clip\CompilationClip;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
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
            ->action(function (RemovalRequest $record): void {
                DB::transaction(static function () use (&$record): void {
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
            ->action(function (RemovalRequest $record): void {
                DB::transaction(static function () use (&$record): void {
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
            ->action(function (RemovalRequest $record): void {
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
            ->action(function (RemovalRequest $record): void {
                DB::transaction(static function () use (&$record): void {
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
            ->modalWidth($requestStatus === RemovalRequestStatus::Approved ? Width::ThreeExtraLarge : null)
            ->schema($requestStatus === RemovalRequestStatus::Approved ? [
                Grid::make(2)
                    ->gap()
                    ->schema([
                        Radio::make('published_compilations')
                            ->disabled(static fn (RemovalRequest $record) => auth()->user()->cannot('updateAny', [Compilation::class]))
                            ->label('admin/resources/removal-requests.actions.resolve-request-accept.schema.published_compilations.label')
                            ->options([
                                0 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.published_compilations.options.nothing.label'),
                                1 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.published_compilations.options.flag.label'),
                                2 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.published_compilations.options.remove.label'),
                            ])
                            ->descriptions([
                                1 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.published_compilations.options.flag.description'),
                                2 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.published_compilations.options.remove.description'),
                            ])
                            ->translateLabel()
                            ->default(1),

                        Radio::make('unpublished_compilations')
                            ->disabled(static fn (RemovalRequest $record) => auth()->user()->cannot('updateAny', [Compilation::class]))
                            ->label('admin/resources/removal-requests.actions.resolve-request-accept.schema.unpublished_compilations.label')
                            ->options([
                                0 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.unpublished_compilations.options.nothing.label'),
                                1 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.unpublished_compilations.options.flag.label'),
                                2 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.unpublished_compilations.options.remove.label'),
                            ])
                            ->descriptions([
                                1 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.unpublished_compilations.options.flag.description'),
                                2 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.unpublished_compilations.options.remove.description'),
                            ])
                            ->translateLabel()
                            ->default(2),

                        Radio::make('internal_compilations')
                            ->disabled(static fn (RemovalRequest $record) => auth()->user()->cannot('updateAny', [Compilation::class]))
                            ->label('admin/resources/removal-requests.actions.resolve-request-accept.schema.internal_compilations.label')
                            ->options([
                                0 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.internal_compilations.options.nothing.label'),
                                1 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.internal_compilations.options.flag.label'),
                                2 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.internal_compilations.options.remove.label'),
                            ])
                            ->descriptions([
                                1 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.internal_compilations.options.flag.description'),
                                2 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.internal_compilations.options.remove.description'),
                            ])
                            ->translateLabel()
                            ->default(0),

                        Radio::make('clip')
                            ->label('admin/resources/removal-requests.actions.resolve-request-accept.schema.clip.label')
                            ->translateLabel()
                            ->default(fn (RemovalRequest $record): int => auth()->user()->can('update', $record->clip) ? 1 : 0)
                            ->disableOptionWhen(fn (int $value, RemovalRequest $record): bool => match ($value) {
                                1 => auth()->user()->cannot('update', $record->clip),
                                2 => auth()->user()->cannot('delete', $record->clip),
                                default => false,
                            })
                            ->options([
                                0 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.clip.options.nothing.label'),
                                1 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.clip.options.block.label'),
                                2 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.clip.options.remove.label'),
                            ])
                            ->descriptions([
                                1 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.clip.options.block.description'),
                                2 => __('admin/resources/removal-requests.actions.resolve-request-accept.schema.clip.options.remove.description'),
                            ]),
                    ]),
            ] : null)
            ->action(function (RemovalRequest $record, array $data = []) use ($requestStatus): void {
                DB::transaction(static function () use ($requestStatus, &$record, &$data): void {
                    $removalRequest = RemovalRequest::query()
                        ->lockForUpdate()
                        ->find($record->id);

                    if (! $removalRequest) {
                        return;
                    }

                    $removalRequest->update([
                        'status' => $requestStatus,
                        'resolved_at' => now(),
                        'resolved_by' => auth()->id(),
                    ]);

                    if (auth()->user()->can('updateAny', [Compilation::class])) {
                        $compilationQueries = [
                            'published_compilations' => $removalRequest->clip->compilations()
                                ->select('compilations.id')
                                ->whereIn('status', CompilationStatus::getVoteDisabledCases()),

                            'unpublished_compilations' => $removalRequest->clip->compilations()
                                ->select('compilations.id')
                                ->whereNotIn('status', [...CompilationStatus::getVoteDisabledCases(), CompilationStatus::Internal]),

                            'internal_compilations' => $removalRequest->clip->compilations()
                                ->select('compilations.id')
                                ->where('status', CompilationStatus::Internal),
                        ];

                        foreach ($compilationQueries as $field => $scopedQuery) {
                            $clipQuery = CompilationClip::query()
                                ->whereIn('compilation_clip.compilation_id', $scopedQuery)
                                ->where('clip_id', $removalRequest->clip_id);

                            match ((int) ($data[$field] ?? 0)) {
                                1 => $clipQuery->update(['removed_at' => now()]),
                                2 => $clipQuery->delete(),
                                default => null,
                            };
                        }
                    }

                    if (auth()->user()->canAny(['update', 'delete'], [$removalRequest->clip])) {
                        match ((int) ($data['clip'] ?? 0)) {
                            1 => auth()->user()->can('update', [$removalRequest->clip]) && $removalRequest->clip->update(['status' => ClipStatus::Blocked]),
                            2 => auth()->user()->can('delete', [$removalRequest->clip]) && $removalRequest->clip->delete(),
                            default => null,
                        };
                    }

                    Notification::make('resolved')
                        ->title(__('admin/resources/removal-requests.notifications.resolved.title', [
                            'status' => $requestStatus->getLabel(),
                        ]))
                        ->success()
                        ->send();
                });

                $record->refresh();
            });
    }
}
