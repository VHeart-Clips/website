<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Closure;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteUserRequest extends FormRequest
{
    protected bool $isOtp = false;

    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'bail',
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $mfa = resolve(AppAuthentication::class);
                    $user = auth()->user();

                    if (! $mfa->isEnabled($user)) {

                        if ($value !== __('user.settings.delete.confirmation.keyword.keyword')) {
                            $fail(__('auth.two-factor.validation.incorrect'));
                        }

                        return;
                    }

                    $this->isOtp = ctype_digit($value);
                    $length = mb_strlen($value);

                    if ($this->isOtp && $length !== 6) {
                        $fail(__('auth.two-factor.validation.otp'));

                        return;
                    }

                    if (! $this->isOtp && $length !== 21) {
                        $fail(__('auth.two-factor.validation.recovery'));

                        return;
                    }

                    $code = $this->input('code', '');

                    if (
                        $this->isOtp
                            ? $mfa->verifyCode($code, $user->app_authentication_secret)
                            : $mfa->verifyRecoveryCode($code, $user)
                    ) {
                        return;
                    }

                    $fail(__('auth.two-factor.validation.incorrect'));
                },
            ],
        ];
    }
}
