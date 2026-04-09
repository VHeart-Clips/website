<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Actions\Moderation;

use App\Enums\Clips\ClipStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use Filament\Actions\Action;

class FlagClipAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->modalDescription('This clip will be hidden from voting and cannot be added to new compilations.')
            ->hidden(fn (Clip $record) => $record->status->isWithheld())
            ->icon(LucideIcon::Flag)
            ->label('Flag Clip')
            ->authorize('flag')
            ->requiresConfirmation()
            ->color('warning')
            ->action(function (Clip $record): void {
                if ($record->status === ClipStatus::NeedApproval) {
                    return;
                }

                $record->update([
                    'status' => ClipStatus::NeedApproval,
                ]);
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'flagClip';
    }
}
