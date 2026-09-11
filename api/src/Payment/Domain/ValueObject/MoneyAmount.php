<?php

declare(strict_types=1);

namespace App\Payment\Domain\ValueObject;

use App\Payment\Domain\Exception\InvalidPaymentAmount;

final readonly class MoneyAmount
{
    private function __construct(private int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        if ($cents < 0) {
            throw new InvalidPaymentAmount();
        }

        return new self($cents);
    }

    public function cents(): int
    {
        return $this->cents;
    }
}
