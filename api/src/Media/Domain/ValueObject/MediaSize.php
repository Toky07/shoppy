<?php

declare(strict_types=1);

namespace App\Media\Domain\ValueObject;

use App\Media\Domain\Exception\InvalidMediaSize;

final readonly class MediaSize
{
    public const MAX_BYTES = 10 * 1024 * 1024;

    private function __construct(private int $value)
    {
    }

    public static function fromInt(int $value): self
    {
        if ($value < 1 || $value > self::MAX_BYTES) {
            throw new InvalidMediaSize();
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
