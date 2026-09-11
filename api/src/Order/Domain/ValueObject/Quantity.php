<?php

declare(strict_types=1);

namespace App\Order\Domain\ValueObject;

use App\Order\Domain\Exception\InvalidOrderQuantity;

final readonly class Quantity
{
    private function __construct(private int $value)
    {
    }

    public static function fromInt(int $value): self
    {
        if ($value < 1) {
            throw new InvalidOrderQuantity();
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
