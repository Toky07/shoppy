<?php

declare(strict_types=1);

namespace App\Order\Domain\ValueObject;

use App\Order\Domain\Exception\InvalidUnitPrice;

final readonly class UnitPrice
{
    private const CURRENCY = 'EUR';

    private function __construct(private int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        if ($cents < 0) {
            throw new InvalidUnitPrice();
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
