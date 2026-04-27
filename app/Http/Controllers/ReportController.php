<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\StoreReportAction;
use App\Http\Requests\Reports\StoreReportRequest;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request, StoreReportAction $storeReportAction): JsonResponse
    {
        $report = $storeReportAction->fromRequest($request);

        return new JsonResponse([
            'reportId' => (string) $report->id,
        ]);
    }
}
