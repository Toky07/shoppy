<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidRating;

final readonly class Rating
{
    private function __construct(private int $value)
    {
    }

    public static function fromInt(int $value): self
    {
        if ($value < 1 || $value > 5) {
            throw new InvalidRating();
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
