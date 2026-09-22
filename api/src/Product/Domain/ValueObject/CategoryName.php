<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidCategoryName;

final readonly class CategoryName
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new InvalidCategoryName();
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }
}
