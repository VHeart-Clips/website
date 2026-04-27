<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\Actions;

use App\Enums\Filament\LucideIcon;
use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use App\Services\Twitch\Data\UserDto;
use App\Services\Twitch\TwitchService;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class UpdateUserAction extends Action
{
    protected ?Closure $userResolver = null;

    protected bool $createBroadcaster = false;

    public function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (Model $record): string => is_int($this->resolveTarget($record)) ? 'Create User' : 'Update User')
            ->authorize(Permission::CanImportUsers)
            ->translateLabel()
            ->icon(fn (Model $record): LucideIcon => is_int($this->resolveTarget($record)) ? LucideIcon::Plus : LucideIcon::RefreshCcw)
            ->requiresConfirmation()
            ->hidden(fn (Model $record): bool => ($resolved = $this->resolveTarget($record)) === null || $resolved === 0 || ($resolved instanceof User && $resolved->id === 0))
            ->action(function (Model $record, array $data, TwitchService $twitchService): void {
                $target = $this->resolveTarget($record);

                /** @var UserDto|null $userDto */
                $userDto = array_first(
                    $twitchService->asSessionUser()->getUsers(['id' => [$target instanceof Model ? $target->id : $target]])
                );

                if (! $userDto) {
                    Notification::make()
                        ->title('Could not update user')
                        ->body('We could not fetch the user from Twitch, try again later.')
                        ->warning()
                        ->send();

                    return;
                }

                match (true) {
                    is_numeric($target) => User::updateOrCreate(['id' => $target], $userDto->toModel()),
                    $target instanceof Broadcaster => $target->user->update($userDto->toModel()),
                    $target instanceof User => $target->update($userDto->toModel()),
                    default => throw new InvalidArgumentException(
                        'Invalid model for UpdateUserAction, only allowed are User, Broadcaster or integer, got '.$target::class
                    ),
                };

                if ($this->createBroadcaster) {
                    match (true) {
                        is_numeric($target) => Broadcaster::updateOrCreate(['id' => $target]),
                        $target instanceof User => Broadcaster::updateOrCreate(['id' => $target->id]),
                        default => null,
                    };
                }
            })
            ->successNotificationTitle('User has been refreshed, avatar may be cached.');
    }

    public static function getDefaultName(): ?string
    {
        return 'updateUser';
    }

    public function resolveUserUsing(Closure $resolver): static
    {
        $this->userResolver = $resolver;

        return $this;
    }

    public function shouldCreateBroadcaster(bool $value = true): static
    {
        $this->createBroadcaster = $value;

        return $this;
    }

    private function resolveTarget(Model $record): Model|int|null
    {
        if (! $this->userResolver instanceof Closure) {
            return $record;
        }

        return $this->evaluate($this->userResolver);
    }
}
