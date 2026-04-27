<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\Pages;

use App\Filament\Actions\ResourceLinkAction;
use App\Filament\AdminPanel\Resources\Users\UserResource;
use App\Filament\AdminPanel\Resources\Users\Widgets\UserBroadcastedClipsWidget;
use App\Filament\AdminPanel\Resources\Users\Widgets\UserCreatedClipsWidget;
use App\Filament\AdminPanel\Resources\Users\Widgets\UserReportsCreatedWidget;
use App\Filament\AdminPanel\Resources\Users\Widgets\UserReportsResolvedWidget;
use App\Filament\AdminPanel\Resources\Users\Widgets\UserSubmittedClipsWidget;
use App\Filament\AdminPanel\Resources\Users\Widgets\UserVotesWidget;
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
            ResourceLinkAction::make('broadcasterLink')
                ->relationship('broadcaster')
                ->label('Broadcaster'),
            CommentsAction::make()
                ->mentionables(fn (Model $record) => User::query()->whereHas('roles')->get())
                ->authorize('comment')
                ->perPage(4)
                ->loadMoreIncrementsBy(8)
                ->modalWidth(Width::SevenExtraLarge),
            Action::make('2fa_reset')
                ->label('Remove 2FA')
                ->hidden(fn (User $record): bool => $record->app_authentication_secret === null)
                ->authorize('update')
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

    protected function getFooterWidgets(): array
    {
        return [
            UserVotesWidget::class,
            UserReportsCreatedWidget::class,
            UserReportsResolvedWidget::class,
            UserSubmittedClipsWidget::class,
            UserCreatedClipsWidget::class,
            UserBroadcastedClipsWidget::class,
        ];
    }
}
