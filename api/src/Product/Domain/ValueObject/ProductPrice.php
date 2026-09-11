<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidProductPrice;

final readonly class ProductPrice
{
    private const CURRENCY = 'EUR';

    private function __construct(private int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        if ($cents < 0) {
            throw new InvalidProductPrice();
        }

        return new self($cents);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function currency(): string
    {
        return self::CURRENCY;
    }
}
