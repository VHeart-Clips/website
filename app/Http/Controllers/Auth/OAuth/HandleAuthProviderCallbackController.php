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
use Illuminate\Support\Facades\Log;
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
            /** @var \Laravel\Socialite\Two\User $twitchUser */
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
        $user = User::query()->find($twitchUser->getId());

        $updateAttributes = [
            'name' => $twitchUser->getName(),
            'avatar_url' => $twitchUser->getAvatar(),
            'twitch_refresh_token' => $twitchUser->refreshToken,
        ];

        if ($user) {
            $user->update($updateAttributes);
        } else {
            $user = User::restoreOrCreate([
                'id' => $twitchUser->getId(),
            ], $updateAttributes);

            if (! $user->wasRecentlyCreated) {
                Log::notice('User has been restored by user itself.', ['user_id' => $user->id]);
            }
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
            ->anonymize(false)
            ->on($user)
            ->save();

        if ($user->wasRecentlyCreated) {
            $request->session()->flash('showTwitchPermissionsPrompt');
        }

        return redirect()->intended(route('home'));
    }
}
