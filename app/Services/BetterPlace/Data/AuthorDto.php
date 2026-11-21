<?php

declare(strict_types=1);

namespace App\Services\BetterPlace\Data;

readonly class AuthorDto
{
    public function __construct(
        public string $name,
    ) {}

    public static function fromArray(?array $data): self|AnonymousAuthorDto
    {
        $name = $data['name'] ?? null;

        if ($name === null) {
            return new AnonymousAuthorDto();
        }

        return new static($name);
    }
}
