<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Bans\Actions;

use App\Actions\Ban\UnbanAction as UnbanModelAction;
use App\Enums\Filament\LucideIcon;
use App\Models\Ban;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class UnbanAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->authorize(fn (?Ban $record) => $record?->isActive()
                && auth()->user()->can('unban', $record->loadMissing('bannable')->bannable)
            )
            ->icon(LucideIcon::Ban)
            ->requiresConfirmation()
            ->color('warning')
            ->label('Unban')
            ->action(function (Ban $record, UnbanModelAction $unbanModelAction): void {
                try {
                    $unbanModelAction->execute(
                        ban: $record,
                        unbannedByUserID: auth()->id(),
                    );

                    Notification::make('unbanned-'.$record->getKey())
                        ->title('Ban Revoked')
                        ->success()
                        ->send();
                } catch (Exception $e) {
                    report($e);

                    Notification::make('error-unbanning-'.$record->getKey())
                        ->title('Could not Revoke Ban')
                        ->body('Error while unbanning, please try again later.')
                        ->danger()
                        ->send();
                }
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'unban';
    }
}
