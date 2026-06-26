<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Filament\Dashboard\Pages\Onboarding;
use App\Models\Broadcaster\Broadcaster;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect the user to their own broadcaster onboarding if they try to access their own stuff without being onboarded
 */
class RequiresBroadcasterProfile
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs(Onboarding::getRouteName())) {
            return $next($request);
        }

        if ($request->route('tenant') !== (string) auth()->id()) {
            return $next($request);
        }

        $user = $request->user();

        if (Broadcaster::where('id', $user->id)->whereOnboarded()->doesntExist()) {
            return redirect()->guest(
                Onboarding::getUrl(panel: 'dashboard', tenant: Broadcaster::placeholder($user->id)),
            );
        }

        return $next($request);
    }
}
