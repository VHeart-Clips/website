<?php

declare(strict_types=1);

namespace App\Enums\Clips;

use App\Enums\Traits\HasHeadlineLabel;
use Filament\Support\Contracts\HasLabel;

enum CompilationStatus: string implements HasLabel
{
    use HasHeadlineLabel;

    /** Collection is not intended for public use */
    case Internal = 'internal';

    /** Collection is a planned video, video is not finished yet though */
    case Planned = 'planned';

    /** Collection belongs to a finished and scheduled video */
    case Scheduled = 'scheduled';

    /** Collection can be accessed publicly but only if you know the url */
    case Unlisted = 'unlisted';

    /** Collection is listed on the homepage */
    case Published = 'published';

    /** Collection is archived, same as public but maybe with label */
    case Archived = 'archived';

    /**
     * All Cases that are considered Public and Visible.
     *
     * @return CompilationStatus[]
     */
    public static function getVisibleCases(): array
    {
        return [
            self::Published,
            self::Archived,
        ];
    }

    /**
     * All cases that are considered public but may not be visible.
     *
     * @return CompilationStatus[]
     */
    public static function getPublicCases(): array
    {
        return [
            self::Published,
            self::Archived,
            self::Unlisted,
        ];
    }

    /**
     * Do we consider this collection public and visible?
     */
    public function isVisible(): bool
    {
        return in_array($this->name, self::getVisibleCases(), true);
    }

    /**
     * Do we consider this collection public?
     */
    public function isPublic(): bool
    {
        return in_array($this->name, self::getPublicCases(), true);
    }
}
