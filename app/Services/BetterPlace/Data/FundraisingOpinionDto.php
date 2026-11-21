<?php

declare(strict_types=1);

namespace App\Services\BetterPlace\Data;

use App\Services\BetterPlace\Data\Traits\InteractsWithCurrency;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

readonly class FundraisingOpinionDto
{
    use InteractsWithCurrency;

    public function __construct(
        public int $id,
        public CarbonInterface $createdAt,
        public CarbonInterface $updatedAt,
        public int $donatedAmountInCents,
        public AuthorDto|AnonymousAuthorDto $author,
        public ?string $message,
        public CarbonInterface $confirmedAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            createdAt: CarbonImmutable::parse($data['created_at']),
            updatedAt: CarbonImmutable::parse($data['updated_at']),
            donatedAmountInCents: $data['donated_amount_in_cents'],
            author: AuthorDto::fromArray($data['author']),
            message: $data['message'] ?? null,
            confirmedAt: CarbonImmutable::parse($data['confirmed_at']),
        );
    }

    public function getDonatedAmountInEuro(): string
    {
        return $this->toCurrencyString($this->donatedAmountInCents);
    }

    public function isAnonymous(): bool
    {
        return $this->author instanceof AnonymousAuthorDto;
    }
}
