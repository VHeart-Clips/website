<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use App\Models\User;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Shows the 2FA Verification Prompt
 */
class TwoFactorPromptController extends Controller implements HasMiddleware
{
    public function __invoke(TwoFactorChallengeRequest $request, AppAuthentication $mfa): InertiaResponse|RedirectResponse
    {
        $userId = $request->getChallengedUserId();
        $user = User::query()->find($userId);

        if (! $userId || ! $user || ! $mfa->isEnabled($user)) {
            return to_route('login');
        }

        return Inertia::render('auth/challenge');
    }

    public static function middleware(): array
    {
        return ['guest'];
    }
}
