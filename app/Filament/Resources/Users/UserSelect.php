<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Models\User;
use App\Services\Twitch\Data\UserDto;
use App\Services\Twitch\TwitchService;
use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Override;

class UserSelect extends Select
{
    protected ?Closure $whereNotExists = null;

    protected ?Closure $ignoredIds = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getSearchResultsUsing(function (string $search, TwitchService $twitchService) {
            if (is_numeric($search)) {
                if ($user = User::find((int) $search)) {
                    return [$user->id => $user->name];
                }

                return collect($twitchService->asSessionUser()->getUsers(['id' => $search]))
                    ->mapWithKeys(fn (UserDto $item): array => [$item->id => $item->displayName]);
            }

            if (preg_match('/(?:.*?\/){3}([a-zA-Z0-9]\w{2,24})\b/', $search, $matches)) {
                $search = $matches[1];
            }

            $search = mb_trim($search);

            $users = collect($twitchService->asSessionUser()->getUsers(['login' => $search]))
                ->each(fn (UserDto $user) => Cache::put("twitch:user:$user->id", $user, now()->addMinutes(30)))
                ->map(fn (UserDto $item): array => ['name' => $item->displayName, 'id' => $item->id]);

            $userQuery = User::where('name', 'ilike', "%$search%");

            if ($this->whereNotExists instanceof Closure) {
                $userQuery->whereNotExists(function (Builder $query): void {
                    $this->evaluate($this->whereNotExists, ['query' => $query]);
                });
            }

            $user = $userQuery->limit(5)
                ->pluck('name', 'id')
                ->map(fn (string $name, int $id): array => ['id' => $id, 'name' => $name])
                ->merge($users)
                ->unique('id')
                ->take(100);

            $existingIds = [];

            if ($this->ignoredIds instanceof Closure) {
                $existingIds = $this->evaluate($this->ignoredIds, [
                    'user' => $user,
                ]);
            }

            return $user->reject(fn (array $item): bool => in_array((int) $item['id'], $existingIds, true))
                ->sortBy(fn (array $item): int => levenshtein(mb_strtolower($search), mb_strtolower((string) $item['name'])))
                ->mapWithKeys(fn (array $item): array => [$item['id'] => $item['name']]);
        })->getOptionLabelUsing(function (string $value, TwitchService $twitchService) {
            if ($name = User::find($value)?->name) {
                return $name;
            }
            if ($user = Cache::get("twitch:user:$value")) {
                $user = User::create($user->toModel());

                return $user->name;
            }
            $users = $twitchService->asSessionUser()->getUsers([
                'id' => $value,
            ]);

            /** @var UserDto $user */
            $user = array_first($users);

            $user = User::create($user->toModel());

            return $user->name;
        })->regex('/(?:https?:(?:.*?\/){3})?[a-zA-Z0-9]\w{2,24}\b/');
    }

    #[Override]
    public static function make(?string $name = null): static
    {
        return parent::make($name)->searchable();
    }

    public function whereNotExists(?Closure $callback): static
    {
        $this->whereNotExists = $callback;

        return $this;
    }

    public function ignoredIds(?Closure $callback): static
    {
        $this->ignoredIds = $callback;

        return $this;
    }
}
