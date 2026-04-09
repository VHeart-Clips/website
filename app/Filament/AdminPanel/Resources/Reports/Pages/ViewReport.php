<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Reports\Pages;

use App\Enums\Filament\LucideIcon;
use App\Enums\Reports\ReportStatus;
use App\Enums\Reports\ResolveAction;
use App\Filament\AdminPanel\Resources\Reports\ReportResource;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;

class ViewReport extends ViewRecord
{
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
                    $record->update([
                        'status' => ReportStatus::InReview,
                        'claimed_by' => auth()->id(),
                        'claimed_at' => now(),
                    ]);
                }),
            Action::make('unclaim')
                ->requiresConfirmation()
                ->label('Unclaim')
                ->icon(LucideIcon::LockOpen)
                ->color('info')
                ->visible(fn (Report $record): bool => $record->claimed_by !== null && $record->status === ReportStatus::InReview)
                ->authorize('claim')
                ->action(function (Report $record): void {
                    $record->update([
                        'status' => ReportStatus::Pending,
                        'claimed_by' => null,
                        'claimed_at' => null,
                    ]);
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
                        ->hint('Required if Action is Other')
                        ->required(fn (Get $get): bool => $get('action') !== null && $get('action') === ResolveAction::Other),
                ])
                ->visible(fn (Report $record): bool => $record->claimed_by === auth()->id() && $record->status === ReportStatus::InReview)
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
        ];
    }
}
