<?php

declare(strict_types=1);

namespace App\View\Components\AboutUs;

use App\Services\BetterPlace\BetterPlaceService;
use App\Services\BetterPlace\Data\FundraisingEventDetailsDto;
use App\Services\BetterPlace\Data\FundraisingOpinionListDto;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class BetterplaceDonationCard extends Component
{
    // After how many seconds do we consider the data stale (and refresh in the background)
    private const int CACHE_TTL_STALE = 300;

    // After how many seconds do we want to force a refresh
    private const int CACHE_TTL_INVALID = 86_400;

    public readonly int $eventId;

    public function __construct()
    {
        $this->eventId = config('services.betterplace.fundraising', 0);
    }

    public function render(): View
    {
        /**
         * @var FundraisingEventDetailsDto $details
         * @var FundraisingOpinionListDto $opinions
         */
        [$details, $opinions, $error] = $this->loadData();

        return view('components.about-us.betterplace-donation-card', [
            'details' => $details,
            'opinions' => $opinions->opinions,
            'error' => $error,
        ]);
    }

    private function loadData(): array
    {
        try {
            return Cache::flexible(
                "betterplace_donation_card_$this->eventId",
                [self::CACHE_TTL_STALE, self::CACHE_TTL_INVALID],
                static function (): array {
                    $eventDetails = BetterPlaceService::getFundraisingDetails();
                    $eventOpinions = BetterPlaceService::getFundraisingOpinions();

                    return [$eventDetails, $eventOpinions, null];
                }
            );
        } catch (Exception $exception) {
            report($exception);

            return [null, null, __('betterplace.error')];
        }
    }
}
