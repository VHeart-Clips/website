<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\Broadcaster\BroadcasterConsentLog;
use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;
use Illuminate\Support\LazyCollection;

class BroadcasterConsentLogsSection extends BaseSection
{
    public function title(): string
    {
        return 'Broadcaster Consent Logs';
    }

    public function shouldExport(User $user): bool
    {
        return BroadcasterConsentLog::query()
            ->where('broadcaster_id', $user->getKey())
            ->exists();
    }

    public function export(User $user): LazyCollection
    {
        return BroadcasterConsentLog::query()
            ->where('broadcaster_id', $user->getKey())
            ->lazyByIdDesc()
            ->map(fn (BroadcasterConsentLog $log): array => [
                'state' => $log->state,
                'changed_by' => match ((int) $log->changed_by) {
                    $user->getKey() => 'you',
                    0 => 'system',
                    default => 'other',
                },
                'change_reason' => $log->change_reason,
                'changed_at' => $log->changed_at->toIso8601ZuluString(),
            ]);
    }
}
