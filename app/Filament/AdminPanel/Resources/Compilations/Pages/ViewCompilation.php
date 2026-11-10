<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Pages;

use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Actions\Management\GenerateTopOverlayAction;
use App\Filament\AdminPanel\Resources\Compilations\CompilationResource;
use App\Jobs\Discord\Compilation\Status\DeleteCompilationStatusWebhookJob;
use App\Models\Clip;
use App\Models\Clip\Compilation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

class ViewCompilation extends ViewRecord
{
    protected static string $resource = CompilationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CommentsAction::make()
                ->mentionables(fn (Model $record) => User::query()->whereHas('roles')->get())
                ->authorize('comment')
                ->perPage(4)
                ->loadMoreIncrementsBy(8)
                ->modalWidth(Width::SevenExtraLarge),
            EditAction::make(),
            ActionGroup::make([
                Action::make('yura-ist-faul')
                    ->label('Broadcaster List')
                    ->icon(LucideIcon::ClipboardCopy)
                    ->modalHeading('Broadcaster List')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->fillForm(function (Compilation $record): array {
                        $list = $record->clips()
                            ->with('broadcaster')
                            ->distinct('broadcaster_id')
                            ->get()
                            ->sortBy(fn (Clip $clip): string => mb_strtolower($clip->broadcaster?->name ?? ''))
                            ->map(fn (Clip $clip): string => " - {$clip->broadcaster?->name} https://twitch.tv/".mb_strtolower($clip->broadcaster?->name ?? ''))
                            ->join("\n");

                        return ['clips_list' => $list];
                    })
                    ->schema([
                        Textarea::make('clips_list')
                            ->hiddenLabel()
                            ->autosize()
                            ->readOnly()
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ]),
                GenerateTopOverlayAction::make(),
            ])
                ->button()
                ->label('Für Yura'),
            ActionGroup::make([
                ActionGroup::make([
                    $this->makeUpdateWebhookMessageAction(),
                    $this->makeCreateWebhookMessageAction(),
                    $this->makeDeleteWebhookMessageAction(),
                ])
                    ->label('Discord'),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ]),
        ];
    }

    private function makeUpdateWebhookMessageAction(): Action
    {
        return Action::make('updateWebhook')
            ->visible(fn (Compilation $record): bool => filled($record->discord_message_id))
            ->action(fn (Compilation $record): bool => $record->dispatchUpdateWebhook())
            ->successNotificationTitle('Update has been scheduled')
            ->authorize('superadmin')
            ->label('Update Status')
            ->icon(LucideIcon::MessageSquareReply)
            ->rateLimit(1)
            ->color('info');
    }

    private function makeCreateWebhookMessageAction(): Action
    {
        return Action::make('createWebhook')
            ->visible(fn (Compilation $record): bool => blank($record->discord_message_id))
            ->action(fn (Compilation $record): bool => $record->dispatchUpdateWebhook(force: true))
            ->successNotificationTitle('status message has been scheduled')
            ->authorize('superadmin')
            ->label('Create Status')
            ->icon(LucideIcon::MessageSquarePlus)
            ->rateLimit(1)
            ->requiresConfirmation()
            ->color('info');
    }

    private function makeDeleteWebhookMessageAction(): Action
    {
        return Action::make('deleteWebhook')
            ->visible(fn (Compilation $record): bool => filled($record->discord_message_id))
            ->action(fn (Compilation $record): bool => (bool) dispatch(new DeleteCompilationStatusWebhookJob($record->discord_message_id)))
            ->successNotificationTitle('status message will be removed')
            ->authorize('superadmin')
            ->label('Delete Status')
            ->icon(LucideIcon::MessageSquareOff)
            ->rateLimit(1)
            ->requiresConfirmation()
            ->color('danger');
    }
}
