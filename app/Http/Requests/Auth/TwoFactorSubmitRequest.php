<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;

class TwoFactorSubmitRequest extends TwoFactorChallengeRequest
{
    public function authorize(): bool
    {
        return $this->session()->has('auth.2fa.id');
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'numeric', 'max_digits:6', 'min_digits:6'],
            'recovery_code' => ['sometimes', 'string'],
        ];
    }

    /**
     * Validates the given code or recovery_code
     *
     * Will also forget the auth.2fa.id session key on success.
     */
    public function ensureCodeIsValid(User $user): void
    {
        $mfa = app(AppAuthentication::class);

        if (
            $mfa->verifyCode($this->input('code', ''), $user->app_authentication_secret) ||
            $mfa->verifyRecoveryCode($this->input('recovery_code', ''), $user)
        ) {
            $this->session()->forget(['auth.2fa.id']);

            return;
        }

        throw ValidationException::withMessages([
            'code' => 'Incorrect code',
            'recovery_code' => 'Incorrect code',
        ]);
    }
}
