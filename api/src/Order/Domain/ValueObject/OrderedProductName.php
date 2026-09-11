<?php

declare(strict_types=1);

namespace App\Order\Domain\ValueObject;

use App\Order\Domain\Exception\InvalidOrderedProductName;

final readonly class OrderedProductName
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new InvalidOrderedProductName();
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }
}
