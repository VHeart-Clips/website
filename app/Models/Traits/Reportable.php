<?php

declare(strict_types=1);

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
     * Override to return the correct title attribute for this model so we can use it to show a human readable name
     */
    public function getReportableTitleAttribute(): string
    {
        return 'name';
    }

    /**
     * Ignore any models with more than $threshold reports
     */
    #[Scope]
    protected function withLimitedReports(Builder $query, int $threshold = 3): void
    {
        $query->where(function ($q) use ($threshold): void {
            $q->doesntHave('reports')
                ->orWhereHas('reports', function (Builder $query): void {
                    $query->where('status', ReportStatus::Pending);
                }, '<=', $threshold);
        });
    }

    #[Scope]
    protected function withActiveReports(Builder $query): void
    {
        $query->whereHas('reports', fn (Builder $query) => $query->where('status', ReportStatus::Pending));
    }
}
