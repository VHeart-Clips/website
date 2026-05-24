<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\TwitchService;
use App\Support\VHeart\Submissions\ClipSubmissionContext;
use App\Support\VHeart\Submissions\ClipSubmissionPipeline;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SubmitClipRequest extends FormRequest
{
    public ?ClipSubmissionContext $context = null;

    public ?string $clipId = null;

    public function __construct(
        protected TwitchService $twitchService,
    ) {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'clip_url' => ['bail', 'required', 'string', 'url'],
            'tags' => ['bail', 'required', 'array', 'min:1', 'max:3'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'parsed_clip_id.required' => __('clips.errors.clip_not_found'),
            'parsed_clip_id.unique' => __('clips.errors.clip_already_known'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $clipId = $this->twitchService->parseClipId($this->input('clip_url'));
                if (! $clipId) {
                    $validator->errors()->add('clip_url', __('clips.errors.clip_not_found'));

                    return;
                }

                $this->context = new ClipSubmissionContext($this->user(), $clipId, $this->twitchService);

                if (! $this->context->clip() instanceof ClipDto) {
                    $validator->errors()->add('clip_url', __('clips.errors.clip_not_found'));

                    return;
                }

                $result = ClipSubmissionPipeline::make($this->twitchService)->check($this->context);

                if (! $result->passed) {
                    $validator->errors()->add('clip_url', $result->message);
                }
            },
        ];
    }
}
