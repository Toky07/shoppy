<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidVariantLabel;

final readonly class VariantLabel
{
    public const MAX_LENGTH = 40;

    private const PATTERN = '/^[\p{L}\p{N}]+(?:[ \'\-][\p{L}\p{N}]+)*$/u';

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value, string $field): self
    {
        $normalized = trim($value);

        if (preg_match(self::PATTERN, $normalized) !== 1 || mb_strlen($normalized) > self::MAX_LENGTH) {
            throw new InvalidVariantLabel($field);
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return mb_strtolower($this->value) === mb_strtolower($other->value);
    }
}
