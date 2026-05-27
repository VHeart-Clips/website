<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Models\User;
use App\Services\Twitch\Contracts\TwitchDtoInterface;
use App\Services\Twitch\Data\UserDto;
use App\Services\Twitch\TwitchService;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class UserSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->searchable()
            ->allowHtml()
            ->label('filament/inputs/user-select.label')
            ->translateLabel()
            ->getSearchResultsUsing(function (string $search, TwitchService $twitchService): array {
                if (blank($search)) {
                    return [];
                }

                if (! RateLimiter::attempt(self::class.':ratelimit:'.auth()->id(), maxAttempts: 5, callback: static fn (): true => true)) {
                    Notification::make('rate-limited')
                        ->warning()
                        ->title(__('filament/inputs/user-select.errors.rate-limited'))
                        ->send();

                    return [];
                }

                $resolved = $this->resolve($search, $twitchService);

                if (! $resolved) {
                    return [];
                }

                $id = (int) $resolved->id;
                $html = $this->buildOptionHtml($resolved);

                return [$id => $html];
            })
            ->getOptionLabelUsing(function (?int $value, TwitchService $twitchService): ?string {
                if (blank($value)) {
                    return null;
                }

                $resolved = $this->resolve($value, $twitchService);

                if (! $resolved) {
                    return null;
                }

                return $this->buildOptionHtml($resolved);
            })
            ->afterStateUpdated(function (?int $state, TwitchService $twitchService): void {
                if (blank($state)) {
                    return;
                }

                if (User::withTrashed()->where('id', $state)->exists()) {
                    return;
                }

                $dto = $this->fetchUserDto($state, $twitchService, 'id');

                if ($dto instanceof UserDto) {
                    User::create($dto->toModel());
                } else {
                    Notification::make('invalid-user')
                        ->danger()
                        ->body(__('filament/inputs/user-select.errors.invalid-user'))
                        ->send();

                    throw new Halt('invalid user provided: '.$state);
                }
            });
    }

    public static function make(?string $name = 'user_id'): static
    {
        return parent::make($name);
    }

    /**
     * Resolves the given input to a local user or twitch dto if possible
     */
    protected function resolve(string|int $input, TwitchService $twitchService): User|UserDto|null
    {
        if (is_int($input)) {
            if ($user = User::whereNot('id', 0)->find($input)) {
                return $user;
            }

            return $this->fetchUserDto($input, $twitchService, 'id');
        }

        if (preg_match('/(?:.*?\/){3}([a-zA-Z0-9]\w{2,24})\b/', $input, $matches)) {
            $input = $matches[1];
        }

        $input = mb_strtolower(mb_trim($input));

        if (! preg_match('/^\w+$/', $input) || mb_strlen($input) < 3) {
            return null;
        }

        if ($user = User::where('name', 'ilike', $input)->whereNot('id', 0)->first()) {
            return $user;
        }

        return $this->fetchUserDto($input, $twitchService);
    }

    protected function fetchUserDto(string|int $value, TwitchService $twitchService, string $param = 'login'): ?UserDto
    {
        $cacheKey = self::class.":$param:$value";

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            /** @var ?UserDto $dto */
            $dto = retry(
                times: 3,
                callback: static fn (): TwitchDtoInterface|Closure|null => Arr::first($twitchService->asSessionUser()->getUsers([$param => $value])),
                sleepMilliseconds: 200,
                when: static fn ($e): bool => $e instanceof ConnectionException,
            );
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        if (! $dto) {
            return null;
        }

        Cache::put(self::class.":login:$dto->login", $dto, now()->addMinutes(30));
        Cache::put(self::class.":id:$dto->id", $dto, now()->addMinutes(30));

        return $dto;
    }

    protected function buildOptionHtml(User|UserDto $user): string
    {
        $name = $user instanceof UserDto ? $user->displayName : $user->name;

        $avatarUrl = $user instanceof UserDto
            ? ($user->profileImageUrl ?? $user->avatar ?? '')
            : ($user->avatar_url ?? $user->avatar ?? '');

        return "<div class='flex items-center gap-2'><img src='$avatarUrl' class='fi-avatar fi-size-md fi-circular object-cover' alt='$name Avatar'/><span class='flex items-center gap-1.5 font-medium'>$name</span></div>";
    }
}
