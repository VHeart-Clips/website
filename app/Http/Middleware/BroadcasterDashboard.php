<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Twitch\TwitchService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

readonly class BroadcasterDashboard
{
    public function __construct(private TwitchService $twitchService)
    {
        $this->twitchService->onUserTokenRefresh(function ($token): void {
            session()->put('twitch_access_token', $token);
        });
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = $request->user();
        $broadcaster = $request->route('user');

        if (empty($broadcaster)) {
            return Redirect::route('dashboard');
        }

        if ($broadcaster->id === $authUser->id) {
            $this->addDashboardData($broadcaster, $authUser);

            return $next($request);
        }

        if (! $broadcaster->clip_permission) {
            return Redirect::route('dashboard');
        }

        if ($this->twitchService->asUser($authUser, session()?->get('twitch_access_token'))->isModeratorFor($broadcaster)) {
            $this->addDashboardData($broadcaster, $authUser);

            return $next($request);
        }

        return Redirect::route('dashboard');
    }

    private function addDashboardData(User $broadcaster, User $authUser): void
    {
        Inertia::share('selectedStreamer', $broadcaster->toResource(UserResource::class));
        Inertia::share('streamers', Inertia::once(function () use ($authUser) {
            Log::info('test middelware');
            $moderatedChanels = $this->twitchService->asUser($authUser, session()?->get('twitch_access_token'))->getModeratedChannels();
            $moderatedChanelIds = array_map(fn (array $item) => $item['broadcaster_id'], $moderatedChanels);

            $channels = User::query()->whereClipPermission(true)->findMany($moderatedChanelIds);

            return $channels->toResourceCollection(UserResource::class);
        }));
    }
}
