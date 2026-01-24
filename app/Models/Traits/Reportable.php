<?php

namespace App\Models\Traits;

use App\Enums\Reports\ReportStatus;
use App\Models\Report;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Model can be Reported
 */
trait Reportable
{
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * Ignore any models with more than $threshold reports
     */
    #[Scope]
    protected function withLimitedReports(Builder $query, int $threshold = 3): void
    {
        $query->where(function ($q) use ($threshold) {
            $q->doesntHave('reports')
                ->orWhereHas('reports', function (Builder $query) {
                    $query->where('status', ReportStatus::Pending);
                }, '<=', $threshold);
        });
    }

    #[Scope]
    protected function withActiveReports(Builder $query): void
    {
        $query->whereHas('reports', function (Builder $query) {
            return $query->where('status', ReportStatus::Pending);
        });
    }
}
