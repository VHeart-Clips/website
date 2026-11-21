<?php

declare(strict_types=1);

namespace App\Services\BetterPlace\Data;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * @see https://github.com/betterplace/betterplace_apidocs/blob/master/sections/fundraising_event_details.md fundraising_event_details.md
 */
readonly class FundraisingOpinionListDto
{
    /**
     * @param  FundraisingOpinionDto[]  $opinions
     */
    public function __construct(
        public int $currentPage,
        public ?int $nextPage,
        public ?int $previousPage,
        public int $offset,
        public int $perPage,
        public int $totalEntries,
        public int $totalPages,
        public array $opinions,
    ) {}

    public static function fromArray(array $data): static
    {
        $opinions = array_map(FundraisingOpinionDto::fromArray(...), $data['data']);

        try {
            $currentPage = $data['current_page'] ?? 1;
            $totalPages = $data['total_pages'] ?? 0;

            $previousPage = $currentPage > 1 ? $currentPage - 1 : null;
            $nextPage = $totalPages > 0 && $currentPage < $totalPages ? $currentPage + 1 : null;

            return new static(
                currentPage: $currentPage,
                nextPage: $nextPage,
                previousPage: $previousPage,
                offset: $data['offset'] ?? 0,
                perPage: $data['per_page'] ?? 0,
                totalEntries: $data['total_entries'] ?? 0,
                totalPages: $totalPages,
                opinions: $opinions
            );
        } catch (Throwable $error) {
            Log::critical('Could not parse BetterPlace FundraisingOpinionList data: '.$error->getMessage());
            report($error);

            return new static(
                currentPage: 1,
                nextPage: null,
                previousPage: null,
                offset: 0,
                perPage: 0,
                totalEntries: 0,
                totalPages: 0,
                opinions: []
            );
        }
    }
}
