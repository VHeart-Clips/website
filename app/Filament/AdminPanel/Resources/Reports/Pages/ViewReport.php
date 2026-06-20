<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Reports\Pages;

use App\Enums\Filament\LucideIcon;
use App\Enums\Reports\ReportStatus;
use App\Enums\Reports\ResolveAction;
use App\Filament\AdminPanel\Resources\Reports\ReportResource;
use App\Jobs\Discord\Reports\DeleteReportWebhookJob;
use App\Jobs\Discord\Reports\ReportWebhookJob;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ViewReport extends ViewRecord
{
    private const int FIRST_WEBHOOK_MESSAGE = 1494679660091539560;

    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('claim')
                ->requiresConfirmation()
                ->label('Claim')
                ->icon(LucideIcon::Lock)
                ->color('info')
                ->visible(fn (Report $record): bool => $record->claimed_by === null && $record->status === ReportStatus::Pending)
                ->authorize('claim')
                ->action(function (Report $record): void {
                    $data = [
                        'status' => ReportStatus::InReview,
                        'claimed_by' => auth()->id(),
                        'claimed_at' => now(),
                    ];

                    $record->update($data);
                    $this->getDuplicates($record, ReportStatus::Pending)
                        ->each(fn (Report $report) => $report->update($data));
                }),

            Action::make('unclaim')
                ->requiresConfirmation()
                ->label('Unclaim')
                ->icon(LucideIcon::LockOpen)
                ->color('warning')
                ->visible(fn (Report $record): bool => $record->claimed_by === auth()->id() && $record->status !== ReportStatus::Resolved)
                ->authorize('claim')
                ->action(function (Report $record): void {
                    $data = [
                        'status' => ReportStatus::Pending,
                        'claimed_by' => null,
                        'claimed_at' => null,
                    ];

                    $record->update($data);
                    $this->getDuplicates($record, ReportStatus::InReview)
                        ->each(fn (Report $report) => $report->update([
                            'status' => ReportStatus::Pending,
                            'claimed_by' => null,
                            'claimed_at' => null,
                        ]));
                }),

            Action::make('resolve')
                ->requiresConfirmation()
                ->label('Resolve')
                ->icon(LucideIcon::Check)
                ->color('success')
                ->modalWidth(Width::ThreeExtraLarge)
                ->schema([
                    Select::make('action')
                        ->required()
                        ->reactive()
                        ->options(ResolveAction::class),
                    MarkdownEditor::make('reason')
                        ->minLength(10)
                        ->maxLength(64 * 1000)
                        ->hint('Required if Action is Other')
                        ->required(fn (Get $get): bool => $get('action') !== null && $get('action') === ResolveAction::Other),
                ])
                ->visible(fn (Report $record): bool => $record->claimed_by === auth()->id() && $record->status !== ReportStatus::Resolved)
                ->authorize('claim')
                ->action(function (Report $record, array $data): void {
                    $record->update([
                        'status' => ReportStatus::Resolved,
                        'resolve_action' => $data['action'],
                        'resolve_description' => $data['reason'],
                        'resolved_by' => auth()->id(),
                        'resolved_at' => now(),
                        'deleted_at' => now(),
                    ]);
                }),

            ActionGroup::make([
                ActionGroup::make([
                    Action::make('notification_create')
                        ->visible(fn (Report $record): bool => $record->discord_message_id === null)
                        ->requiresConfirmation()
                        ->schema([
                            TextInput::make('message_id')
                                ->belowContent('This will create a new message if no id was provided. can be used to link old messages, please provide a valid one though.')
                                ->placeholder('1517223504511111339')
                                ->label('Existing Message ID (optional)')
                                ->minValue(0)
                                ->rule(
                                    Rule::numeric()
                                        ->integer()
                                        ->min(self::FIRST_WEBHOOK_MESSAGE)
                                        ->max(PHP_INT_MAX)
                                )
                                ->unique(column: 'discord_message_id')
                                ->nullable(),
                        ])
                        ->action(function (Report $record, array $data): void {
                            if (is_numeric($data['message_id'] ?? null)) {
                                $record->update([
                                    'discord_message_id' => (int) $data['message_id'],
                                ]);

                                Notification::make('discord_notification_id_set')
                                    ->title('Message Id Set for Report')
                                    ->body('We will use the provided message id from now on')
                                    ->success()
                                    ->send();
                            }

                            ReportWebhookJob::dispatch($record);

                            Notification::make('discord_notification_scheduled_for_create')
                                ->title('Notification Scheduled')
                                ->body('The Notification will be created soon')
                                ->success()
                                ->send();
                        })
                        ->label('Create Notification')
                        ->icon(LucideIcon::BellPlus)
                        ->rateLimit(1),

                    Action::make('notification_update')
                        ->visible(fn (Report $record): bool => $record->discord_message_id !== null)
                        ->action(function (Report $record): void {
                            ReportWebhookJob::dispatch($record);

                            Notification::make('discord_notification_scheduled_for_update')
                                ->title('Notification Scheduled')
                                ->body('The Notification has been scheduled for update.')
                                ->success()
                                ->send();
                        })
                        ->label('Update Notification')
                        ->icon(LucideIcon::RefreshCcw)
                        ->rateLimit(1),

                    Action::make('notification_delete')
                        ->requiresConfirmation()
                        ->visible(fn (Report $record): bool => $record->discord_message_id !== null)
                        ->action(function (Report $record): void {
                            DeleteReportWebhookJob::dispatch($record->discord_message_id, $record);

                            Notification::make('discord_notification_scheduled_for_removal')
                                ->title('Removal Scheduled')
                                ->body('The Discord notification for this Report will be removed, this may take a moment.')
                                ->success()
                                ->send();
                        })
                        ->label('Delete Notification')
                        ->icon(LucideIcon::Trash2)
                        ->color('danger')
                        ->rateLimit(1),
                ])
                    ->color('info')
                    ->label('Discord Notifications')
                    ->icon('simpleicon-discord'), // No Enum :(

                Action::make('force_unclaim')
                    ->disabled(fn (Report $record): bool => ! $record->claimed_by)
                    ->requiresConfirmation()
                    ->label('Force Unclaim')
                    ->icon(LucideIcon::LockOpen)
                    ->color('danger')
                    ->authorize('superadmin')
                    ->action(function (Report $record): void {
                        $record->update([
                            'claimed_by' => null,
                            'claimed_at' => null,
                        ]);
                    }),
                Action::make('reopen')
                    ->disabled(fn (Report $record): bool => $record->status !== ReportStatus::Resolved && $record->resolved_at === null)
                    ->requiresConfirmation()
                    ->label('Re-Open')
                    ->icon(LucideIcon::Recycle)
                    ->color('warning')
                    ->authorize('superadmin')
                    ->action(function (Report $record): void {
                        $record->update([
                            'status' => ReportStatus::Pending,
                            'resolved_by' => null,
                            'resolved_at' => null,
                            'deleted_at' => null,
                        ]);
                    }),
                DeleteAction::make()->authorize('superadmin'),
                RestoreAction::make()->authorize('superadmin'),
            ]),
        ];
    }

    private function getDuplicates(Report $record, ReportStatus $status): Collection
    {
        return Report::query()
            ->where('status', $status)
            ->where('reportable_type', $record->reportable_type)
            ->where('reportable_id', $record->reportable_id)
            ->when($status === ReportStatus::InReview, fn (Builder $q): Builder => $q->where('claimed_by', auth()->id()))
            ->get();
    }
}
