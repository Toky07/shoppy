<?php

declare(strict_types=1);

namespace App\Invoice\Domain\ValueObject;

use App\Invoice\Domain\Exception\InvalidInvoiceAmount;

final readonly class MoneyAmount
{
    private function __construct(private int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        if ($cents < 0) {
            throw new InvalidInvoiceAmount();
        }

        return new self($cents);
    }

    public function cents(): int
    {
        return $this->cents;
    }
}
