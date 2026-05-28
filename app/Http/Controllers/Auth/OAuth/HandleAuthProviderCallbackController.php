<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\OAuth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit\Auditor;
use Carbon\CarbonInterval;
use Exception;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Laravel\Socialite\Facades\Socialite;

/**
 * Handle the response from the OAuth Provider.
 *
 *  Name is neutral as we may allow other providers in the future
 */
#[Middleware('guest')]
#[Middleware('throttle:login')]
class HandleAuthProviderCallbackController extends Controller
{
    public function __invoke(Request $request, AppAuthentication $mfa): RedirectResponse
    {
        try {
            $twitchUser = Socialite::driver('twitch')->user();
        } catch (Exception) {
            return to_route('login')
                ->with('error', __('auth.oauth_error_try_again'));
        }

        // Deny access if twitch account is too fresh for us
        $userCreatedAt = Date::parse($twitchUser->user['created_at']);
        $userAgeMinimum = CarbonInterval::fromString(config('auth.required_account_age'));

        if ($userCreatedAt->add($userAgeMinimum)->isFuture()) {
            return to_route('login')
                ->withErrors(['login' => __('auth.account_created_too_early')]);
        }

        /** @var ?User $user */
        $user = User::withTrashed()->find($twitchUser->getId());

        // If the user was "banned" (soft deleted) clear out refresh token and deny access
        if ($user?->trashed()) {
            if ($user->twitch_refresh_token !== null) {
                $user->update([
                    'twitch_refresh_token' => null,
                ]);
            }

            Auditor::make()
                ->event('auth.login.denied')
                ->on($user)
                ->save();

            return to_route('login')
                ->withErrors(['login' => __('user.disabled')]);
        }

        // Otherwise update (or create) the user
        $updateAttributes = [
            'name' => $twitchUser->getName(),
            'avatar_url' => $twitchUser->getAvatar(),
            'twitch_refresh_token' => $twitchUser->refreshToken,
        ];

        if ($user) {
            $user->update($updateAttributes);
        } else {
            $user = User::create([
                'id' => $twitchUser->getId(),
                ...$updateAttributes,
            ]);
        }

        // Authenticate the user
        $request->session()->regenerate();
        $request->session()->put('twitch_access_token', $twitchUser->token);

        if ($mfa->isEnabled($user)) {
            $request->session()->flash('auth_2fa_id', $user->id);

            return to_route('auth.challenge');
        }

        Auth::login($user);

        Auditor::make()
            ->event('auth.login.success')
            ->anonymize(true)
            ->on($user)
            ->save();

        if ($user->wasRecentlyCreated) {
            $request->session()->flash('showTwitchPermissionsPrompt');
        }

        return redirect()->intended(route('home'));
    }
}
