<?php

declare(strict_types=1);

namespace App\Email\Domain\ValueObject;

use App\Email\Domain\Exception\InvalidEmailAddress;

final readonly class EmailAddress
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtolower(trim($value));

        if (
            $normalized === ''
            || str_contains($normalized, "\n")
            || str_contains($normalized, "\r")
            || filter_var($normalized, FILTER_VALIDATE_EMAIL) === false
        ) {
            throw new InvalidEmailAddress();
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }
}
