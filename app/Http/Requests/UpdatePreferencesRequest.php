<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserSettings\NewGameAvoidancePreference;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreferencesRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'show_on_leaderboard' => [
                'required',
                'boolean',
            ],
            'avoid_spoilers' => [
                'required',
                'boolean',
            ],
            'avoid_new_games' => [
                'nullable',
                Rule::enum(NewGameAvoidancePreference::class),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'show_on_leaderboard' => $this->boolean('show_on_leaderboard'),
            'avoid_spoilers' => $this->boolean('avoid_spoilers'),
        ]);
    }
}
