<?php

declare(strict_types=1);

namespace App\Services\BetterPlace\Data;

use App\Services\BetterPlace\Traits\InteractsWithCurrency;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * @see https://github.com/betterplace/betterplace_apidocs/blob/master/sections/fundraising_event_details.md fundraising_event_details.md
 */
readonly class FundraisingEventDetailsDto
{
    use InteractsWithCurrency;

    public function __construct(
        public int $id,
        public CarbonInterface $createdAt,
        public CarbonInterface $updatedAt,
        public CarbonInterface $contentUpdatedAt,
        public string $title,
        public string $description,
        public bool $donationsProhibited,
        public ?CarbonInterface $closedAt,
        public ?CarbonInterface $activateDonationsAt,
        public int $donationsCount,
        public int $donatedAmountInCents,
        public ?int $requestedAmountInCents,
        public int $forwardedAmountInCents,
        public ?float $progress,
        public AuthorDto $contact,
    ) {}

    public static function fromArray(array $data): static
    {
        try {
            return new static(
                id: $data['id'],
                createdAt: CarbonImmutable::parse($data['created_at']),
                updatedAt: CarbonImmutable::parse($data['updated_at']),
                contentUpdatedAt: CarbonImmutable::parse($data['content_updated_at']),
                title: $data['title'],
                description: $data['description'],
                donationsProhibited: $data['donations_prohibited'],
                closedAt: filled($data['closed_at']) ? CarbonImmutable::parse($data['closed_at']) : null,
                activateDonationsAt: filled($data['activate_donations_at']) ? CarbonImmutable::parse($data['activate_donations_at']) : null,
                donationsCount: $data['donations_count'],
                donatedAmountInCents: $data['donated_amount_in_cents'],
                requestedAmountInCents: $data['requested_amount_in_cents'] ?? null,
                forwardedAmountInCents: $data['forwarded_amount_in_cents'],
                progress: $data['progress_percentage'] ?? null,
                contact: AuthorDto::fromArray($data['contact'])
            );
        } catch (Throwable $error) {
            Log::critical('Could not parse BetterPlace FundraisingEventDetails data: '.$error->getMessage());
            report($error);

            return new static(
                id: -1,
                createdAt: now(),
                updatedAt: now(),
                contentUpdatedAt: now(),
                title: 'Placeholder Fundraising',
                description: 'There was an error while fetching the Fundraising, please try again later.',
                donationsProhibited: true,
                closedAt: now(),
                activateDonationsAt: null,
                donationsCount: 0,
                donatedAmountInCents: 0,
                requestedAmountInCents: null,
                forwardedAmountInCents: 0,
                progress: null,
                contact: new AuthorDto('Error')
            );
        }
    }

    public function getDonatedAmountInEuro(): string
    {
        return $this->toCurrencyString($this->donatedAmountInCents);
    }

    public function getRequestedAmountInEuro(): string
    {
        return $this->toCurrencyString($this->requestedAmountInCents ?? 0);
    }

    public function getForwardedAmountInEuro(): string
    {
        return $this->toCurrencyString($this->forwardedAmountInCents ?? 0);
    }

    public function hasRequestedAmount(): bool
    {
        return $this->requestedAmountInCents !== null;
    }

    public function hasPendingAmount(): bool
    {
        return $this->forwardedAmountInCents < $this->donatedAmountInCents;
    }

    public function hasReachedRequestedAmount(): bool
    {
        if (! $this->hasRequestedAmount()) {
            return false;
        }

        return $this->requestedAmountInCents >= $this->donatedAmountInCents;
    }
}
