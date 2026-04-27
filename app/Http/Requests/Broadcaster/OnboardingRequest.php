<?php

declare(strict_types=1);

namespace App\Http\Requests\Broadcaster;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Clips\ClipStatus;
use App\Enums\FeatureFlag;
use App\Support\FeatureFlag\Feature;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && Feature::isActive(FeatureFlag::BroadcasterOnboarding);
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'consent' => ['nullable', 'array'],
            'default_clip_status' => ['nullable', Rule::enum(ClipStatus::class), Rule::in(ClipStatus::defaultableOptions())],
            'consent.*' => ['required', Rule::enum(BroadcasterConsent::class)],
            'everyone' => ['nullable', 'boolean'],
            'vips' => ['nullable', 'boolean'],
            'moderators' => ['nullable', 'boolean'],
        ];
    }
}
