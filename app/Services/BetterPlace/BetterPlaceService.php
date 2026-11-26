<?php

declare(strict_types=1);

namespace App\Services\BetterPlace;

use App\Enums\UserSettings\LanguagePreference;
use App\Services\BetterPlace\Data\FundraisingEventDetailsDto;
use App\Services\BetterPlace\Data\FundraisingOpinionListDto;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * BetterPlace Public V4 API
 *
 * @see https://github.com/betterplace/betterplace_apidocs/blob/master/api_v4_readme.md#public-api API Documentation
 */
class BetterPlaceService
{
    public static function client(?LanguagePreference $preference = null): PendingRequest
    {
        $lang = self::getLanguage($preference);

        return Http::baseUrl("https://api.betterplace.org/$lang->value/api_v4")
            ->connectTimeout(3)
            ->timeout(5)
            ->acceptJson();
    }

    /**
     * results are cached from 1 to 24 hours
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public static function getFundraisingDetails(
        ?LanguagePreference $preference = null,
    ): FundraisingEventDetailsDto {
        $id = config('services.betterplace.fundraising', 0);
        $lang = self::getLanguage($preference);

        $data = Cache::flexible(
            "betterplace.$lang->value.fundraising.$id.details",
            [now()->addHour(), now()->addDay()],
            static fn () => self::client($lang)
                ->retry(3, 250)
                ->get("/fundraising_events/$id.json")
                ->throw()
                ->json()
        );

        return FundraisingEventDetailsDto::fromArray($data);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public static function getFundraisingOpinions(
        array $order = ['confirmed_at:DESC'],
        array $facets = [],
        int $page = 1,
        int $perPage = 20,
        ?LanguagePreference $preference = null): FundraisingOpinionListDto
    {
        $id = config('services.betterplace.fundraising', 0);

        $data = self::client($preference)
            ->withQueryParameters([
                'page' => max($page, 1),
                'per_page' => clamp($perPage, 1, 200),
                'order' => implode('|', $order),
                'facets' => implode('|', $facets),
            ])
            ->get("/fundraising_events/$id/opinions.json")
            ->throw()
            ->json();

        return FundraisingOpinionListDto::fromArray($data);
    }

    protected static function getLanguage(?LanguagePreference $preference = null): LanguagePreference
    {
        return $preference ?? LanguagePreference::from(app()->currentLocale());
    }
}
