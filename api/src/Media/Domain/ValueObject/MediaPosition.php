<?php

declare(strict_types=1);

namespace App\Media\Domain\ValueObject;

use App\Media\Domain\Exception\InvalidMediaPosition;

final readonly class MediaPosition
{
    private function __construct(private int $value)
    {
    }

    public static function fromInt(int $value): self
    {
        if ($value < 0) {
            throw new InvalidMediaPosition();
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
