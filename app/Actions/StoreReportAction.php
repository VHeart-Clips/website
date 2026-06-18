<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Reports\ReportReason;
use App\Http\Requests\Reports\StoreReportRequest;
use App\Jobs\Discord\ReportWebhookJob;
use App\Jobs\Reports\CheckForRemovedClipJob;
use App\Models\Clip;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StoreReportAction
{
    public function fromRequest(StoreReportRequest $request): Report
    {
        $clip = Clip::findOrFail($request->input('reportable_id'));

        return $this->execute(
            reportable: $clip,
            reason: $request->enum('reason', ReportReason::class),
            user: $request->user(),
            description: $request->input('description'),
        );
    }

    public function execute(Model $reportable, ReportReason $reason, User $user, ?string $description = null): Report
    {
        $report = Report::create([
            'user_id' => $user->getKey(),
            'reportable_type' => $reportable->getMorphClass(),
            'reportable_id' => $reportable->getKey(),
            'reason' => $reason,
            'description' => $description,
        ]);

        ReportWebhookJob::dispatch($report);

        $clip = $reportable instanceof Clip ? $reportable : null;
        if ($reason === ReportReason::ContentUnavailable && $clip instanceof Clip) {
            $report->update(['claimed_by' => 0, 'claimed_at' => now()]);
            CheckForRemovedClipJob::dispatch($clip, $report);
        }

        return $report;
    }
}
