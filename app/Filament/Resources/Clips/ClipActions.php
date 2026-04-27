<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips;

use App\Enums\Filament\LucideIcon;
use App\Filament\Actions\ReportAction;
use App\Models\Clip;
use Filament\Actions\ActionGroup;

class ClipActions
{
    public static function reportableActionGroup(): ActionGroup
    {
        return ActionGroup::make([
            ReportAction::make()
                ->hidden(fn (Clip $record): bool => ! $record->broadcaster_id || $record->broadcaster_id === 0 || $record->broadcaster_id === auth()->id())
                ->icon(LucideIcon::Film),
            ReportAction::make('report_broadcaster')
                ->hidden(fn (Clip $record): bool => ! $record->broadcaster || $record->broadcaster_id === 0 || $record->broadcaster_id === auth()->id())
                ->reportable(fn (Clip $record) => $record->broadcaster)
                ->reportableAlias('Broadcaster')
                ->icon(LucideIcon::Video),
            ReportAction::make('report_submitter')
                ->hidden(fn (Clip $record): bool => ! $record->submitter
                    || $record->submitter_id === 0
                    || $record->submitter_id === $record->creator_id
                    || $record->broadcaster_id === $record->creator_id
                    || $record->submitter_id === auth()->id()
                )
                ->reportable(fn (Clip $record) => $record->submitter)
                ->reportableAlias('Submitter')
                ->icon(LucideIcon::User),
            ReportAction::make('report_clipper')
                ->hidden(fn (Clip $record): bool => ! $record->creator
                    || $record->broadcaster_id === $record->creator_id
                    || $record->creator_id === auth()->id()
                )
                ->reportable(fn (Clip $record) => $record->creator)
                ->reportableAlias('Clipper')
                ->icon(LucideIcon::Scissors),
        ])
            ->label('Report')
            ->color('danger')
            ->icon(LucideIcon::Flag);
    }
}
