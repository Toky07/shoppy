<?php

declare(strict_types=1);

namespace App\Cart\Domain\ValueObject;

use App\Cart\Domain\Exception\InvalidCartQuantity;

final readonly class CartQuantity
{
    private function __construct(private int $value)
    {
    }

    public static function fromInt(int $value): self
    {
        if ($value < 1) {
            throw new InvalidCartQuantity();
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function add(self $other): self
    {
        return self::fromInt($this->value + $other->value);
    }
}
