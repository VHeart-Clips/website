<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Actions\Moderation;

use App\Enums\Clips\ClipStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use Filament\Actions\Action;

class UnflagClipAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->visible(fn (Clip $record) => $record->status->isWithheld())
            ->icon(LucideIcon::Flag)
            ->label('Unflag Clip')
            ->authorize('unflag')
            ->requiresConfirmation()
            ->color('success')
            ->action(function (Clip $record): void {
                if ($record->status === ClipStatus::Unknown) {
                    return;
                }

                $record->update([
                    'status' => ClipStatus::Unknown,
                ]);
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'unflagClip';
    }
}
