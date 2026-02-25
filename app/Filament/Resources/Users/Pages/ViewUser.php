<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Enums\Permission;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CommentsAction::make()
                ->mentionables(fn (Model $record) => User::query()->whereHas('roles')->get())
                ->hidden(fn (): bool => ! auth()->user()->can(Permission::ViewAnyComment))
                ->perPage(4)
                ->loadMoreIncrementsBy(8)
                ->modalWidth(Width::SevenExtraLarge),
            Action::make('2fa_reset')
                ->label('Remove 2FA')
                ->hidden(function (User $record): bool {
                    if (! auth()->user()->can(Permission::UpdateAnyUser)) {
                        return true;
                    }

                    return $record->app_authentication_secret === null;
                })
                ->requiresConfirmation()
                ->action(function (User $user): void {
                    $user->update([
                        'app_authentication_secret' => null,
                        'app_authentication_recovery_codes' => null,
                    ]);
                }),
            EditAction::make(),
            DeleteAction::make()->label('Disable User'),
            ForceDeleteAction::make(),
            RestoreAction::make()->label('Restore User'),
        ];
    }
}
