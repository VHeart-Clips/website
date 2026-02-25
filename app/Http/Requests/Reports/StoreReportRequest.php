<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Enums\Reports\ReportReason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $morphKeys = array_keys(Relation::morphMap());

        return [
            'reportable_type' => [
                'bail',
                'required',
                Rule::in($morphKeys),
            ],
            'reportable_id' => [
                'required',
                Rule::exists(Relation::getMorphedModel($this->input('reportable_type')), 'id'),
            ],
            'reason' => [
                'required',
                Rule::enum(ReportReason::class),
            ],
            'description' => [
                'nullable',
                Rule::requiredIf(
                    fn (): bool => $this->enum('reason', ReportReason::class) === ReportReason::Other,
                ),
                'string',
                'max:1000',
            ],
        ];
    }
}
