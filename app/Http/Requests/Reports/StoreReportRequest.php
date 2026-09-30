<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Enums\Reports\ReportCategoryDetailsType;
use App\Enums\Reports\ReportCategoryReportableType;
use App\Models\ReportCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

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
        $reportableType = $this->enum('reportable_type', ReportCategoryReportableType::class);

        return [
            'reportable_type' => [
                'required',
                Rule::enum(ReportCategoryReportableType::class),
            ],
            'reportable_id' => [
                'required',
                Rule::exists(Relation::getMorphedModel($reportableType?->value), 'id'),
            ],
            'reason' => [
                'bail',
                'required',
                'integer',
                Rule::exists('report_categories', 'id')->where(
                    fn (Builder $query) => $query->where(
                        fn (Builder $query) => $query
                            ->whereNull('reportable_types')
                            ->orWhereJsonLength('reportable_types', 0)
                            ->orWhereJsonContains('reportable_types', $reportableType),
                    ),
                ),
            ],
            'description' => [
                'bail',
                'nullable',
                Rule::requiredIf(
                    fn (): bool => ReportCategory::query()
                        ->where('id', $this->integer('reason'))
                        ->where('details_type', ReportCategoryDetailsType::Required)
                        ->exists(),
                ),
                // TODO: uncomment if frontend has been refactored to support this flag properly
                // Rule::prohibitedIf(
                //     fn (): bool => ReportCategory::query()
                //         ->where('id', $this->integer('reason'))
                //         ->where('details_type', ReportCategoryDetailsType::Disabled)
                //         ->exists(),
                // ),
                'string',
                'max:1000',
            ],
        ];
    }
}
