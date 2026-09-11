<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidProductSort;

final readonly class ProductSort
{
    public const NEWEST = 'newest';
    public const OLDEST = 'oldest';
    public const PRICE_ASC = 'price_asc';
    public const PRICE_DESC = 'price_desc';
    public const NAME_ASC = 'name_asc';

    private const VALUES = [
        self::NEWEST,
        self::OLDEST,
        self::PRICE_ASC,
        self::PRICE_DESC,
        self::NAME_ASC,
    ];

    private function __construct(private string $value)
    {
    }

    public static function newest(): self
    {
        return new self(self::NEWEST);
    }

    public static function fromString(?string $value): self
    {
        if ($value === null || $value === '') {
            return self::newest();
        }

        if (!in_array($value, self::VALUES, true)) {
            throw new InvalidProductSort();
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
