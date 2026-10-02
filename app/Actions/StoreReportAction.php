<?php

declare(strict_types=1);

namespace App\Actions;

use App\Http\Requests\Reports\StoreReportRequest;
use App\Jobs\Reports\CheckForRemovedClipJob;
use App\Models\Clip;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StoreReportAction
{
    // for now we just hardcode that id, later we can decide if it is worth it to change that to a dynamic thing
    private const int CLIP_REMOVED_CATEGORY_ID = 6;

    public function fromRequest(StoreReportRequest $request): Report
    {
        $clip = Clip::findOrFail($request->input('reportable_id'));
        $category = ReportCategory::find($request->integer('reason'));

        return $this->execute(
            reportable: $clip,
            reason: $category,
            user: $request->user(),
            description: $request->input('description'),
        );
    }

    public function execute(Model $reportable, ReportCategory $reason, User $user, ?string $description = null): Report
    {
        $report = Report::create([
            'user_id' => $user->getKey(),
            'reportable_type' => $reportable->getMorphClass(),
            'reportable_id' => $reportable->getKey(),
            'category_id' => $reason->id,
            'description' => $description,
        ]);

        $clip = $reportable instanceof Clip ? $reportable : null;

        if ($reason->id === self::CLIP_REMOVED_CATEGORY_ID && $clip instanceof Clip) {
            $report->update(['claimed_by' => 0, 'claimed_at' => now()]);
            CheckForRemovedClipJob::dispatch($clip, $report);
        }

        return $report;
    }
}
