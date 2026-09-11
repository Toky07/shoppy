<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidProductStock;

final readonly class StockQuantity
{
    private function __construct(private int $value)
    {
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromInt(int $value): self
    {
        if ($value < 0) {
            throw new InvalidProductStock();
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function add(int $delta): self
    {
        return self::fromInt($this->value + $delta);
    }

    public function subtract(int $quantity): self
    {
        return self::fromInt($this->value - $quantity);
    }

    public function isAtLeast(int $quantity): bool
    {
        return $this->value >= $quantity;
    }
}
