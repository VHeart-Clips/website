<?php

declare(strict_types=1);

namespace App\Enums\Compilation;

use App\Enums\Traits\HasHeadlineLabel;
use Filament\Support\Contracts\HasLabel;

enum CompilationProcessingStatus: string implements HasLabel
{
    use HasHeadlineLabel;

    case Scheduled = 'scheduled';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Expired = 'expired';

    /**
     * Returns true if the status is any "not done yet" state
     */
    public function isProcessing(): bool
    {
        return match ($this) {
            self::Scheduled, self::Processing => true,
            default => false,
        };
    }
}
