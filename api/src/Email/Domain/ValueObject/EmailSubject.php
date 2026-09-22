<?php

declare(strict_types=1);

namespace App\Email\Domain\ValueObject;

use App\Email\Domain\Exception\InvalidEmailSubject;

final readonly class EmailSubject
{
    private const int MAX_LENGTH = 150;

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if (
            $normalized === ''
            || str_contains($normalized, "\n")
            || str_contains($normalized, "\r")
            || mb_strlen($normalized) > self::MAX_LENGTH
        ) {
            throw new InvalidEmailSubject();
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }
}
