<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidProductPriceFilter;
use App\Product\Domain\Exception\InvalidProductSearch;

final readonly class ProductListCriteria
{
    public const MAX_SEARCH_LENGTH = 100;

    private function __construct(
        public ?string $search,
        public ProductSort $sort,
        public ?int $minPriceCents,
        public ?int $maxPriceCents,
        public bool $inStockOnly,
        public ?CategoryId $categoryId,
    ) {
    }

    public static function default(): self
    {
        return new self(null, ProductSort::newest(), null, null, false, null);
    }

    public static function fromInput(
        ?string $search,
        ?string $sort,
        ?int $minPriceCents = null,
        ?int $maxPriceCents = null,
        bool $inStockOnly = false,
        ?CategoryId $categoryId = null,
    ): self {
        self::assertPriceRange($minPriceCents, $maxPriceCents);

        return new self(
            self::normalizeSearch($search),
            ProductSort::fromString($sort),
            $minPriceCents,
            $maxPriceCents,
            $inStockOnly,
            $categoryId,
        );
    }

    private static function assertPriceRange(?int $minPriceCents, ?int $maxPriceCents): void
    {
        if ($minPriceCents !== null && $minPriceCents < 0) {
            throw new InvalidProductPriceFilter('minPrice', 'The minimum price cannot be negative.');
        }

        if ($maxPriceCents !== null && $maxPriceCents < 0) {
            throw new InvalidProductPriceFilter('maxPrice', 'The maximum price cannot be negative.');
        }

        if ($minPriceCents !== null && $maxPriceCents !== null && $maxPriceCents < $minPriceCents) {
            throw new InvalidProductPriceFilter('maxPrice', 'The maximum price must be at least the minimum price.');
        }
    }

    public function hasSearch(): bool
    {
        return $this->search !== null;
    }

    private static function normalizeSearch(?string $search): ?string
    {
        if ($search === null) {
            return null;
        }

        $normalized = trim($search);

        if ($normalized === '') {
            return null;
        }

        if (strlen($normalized) > self::MAX_SEARCH_LENGTH) {
            throw new InvalidProductSearch();
        }

        return $normalized;
    }
}
