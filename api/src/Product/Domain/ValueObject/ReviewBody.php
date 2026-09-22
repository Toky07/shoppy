<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidReviewBody;

final readonly class ReviewBody
{
    public const MAX_LENGTH = 1000;

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if ($normalized === '' || mb_strlen($normalized) > self::MAX_LENGTH) {
            throw new InvalidReviewBody();
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }
}
