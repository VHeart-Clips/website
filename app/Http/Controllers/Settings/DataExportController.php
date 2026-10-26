<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Support\Audit\Auditor;
use App\Support\VHeart\DataExport\DataExporter;
use App\Support\VHeart\DataExport\ExportSection;
use App\Support\VHeart\DataExport\Sections\AccountSection;
use Illuminate\Http\Request;
use JsonException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class DataExportController extends Controller
{
    /** @var list<class-string<ExportSection>> */
    public const array SECTIONS = [
        AccountSection::class,
    ];

    /**
     * @throws JsonException|Throwable
     */
    public function __invoke(Request $request): BinaryFileResponse
    {
        $user = $request->user();

        Auditor::make()
            ->event('data-export')
            ->causer($user)
            ->on($user)
            ->save();

        $exporter = new DataExporter(array_map(app(...), self::SECTIONS));

        return response()
            ->download(
                $exporter->export($user),
                "$user->id-export-data-".now()->format('Y-m-d_H-i-s').'.zip',
            )
            ->deleteFileAfterSend();
    }
}
