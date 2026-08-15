<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Vote;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class VoteChangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'voteId' => ['bail', 'required', 'integer', 'exists:votes,id'],
            'voted' => ['required', 'bool'],
        ];
    }

    public function after(): array
    {

        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }
                $voteId = $this->integer('voteId');

                $allowedVoteToChange = Vote::query()
                    ->where('user_id', $this->user()->id)
                    ->where('created_at', '>=', now()->sub(config('vheart.clips.voting.maximum_change_age')))
                    ->has('clip')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->first() ?? null;

                if ($allowedVoteToChange?->id !== $voteId) {

                    $validator->errors()->add('voteId', __('vote.errors.change_not_allowed'));

                    return;
                }
            },
        ];
    }
}
