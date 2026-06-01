<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorSubmitRequest;
use App\Models\User;
use App\Support\Audit\Auditor;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Verifies the 2FA input is valid and authenticates the user if so
 */
#[Middleware('guest')]
#[Middleware('throttle:two-factor')]
class TwoFactorVerificationController extends Controller
{
    public function __invoke(TwoFactorSubmitRequest $request, AppAuthentication $mfa): RedirectResponse
    {
        $userId = $request->getChallengedUserId();
        $user = User::query()->find($userId);

        if (! $user || ! $mfa->isEnabled($user)) {
            return to_route('login');
        }

        $request->ensureCodeIsValid($user);
        $request->session()->regenerate();
        Auth::login($user);

        Auditor::make()
            ->event('auth.login.success')
            ->anonymize(false)
            ->on($user)
            ->save();

        return redirect()->intended(route('home'));
    }
}
