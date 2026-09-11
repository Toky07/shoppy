<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidProductSearch;

final readonly class ProductListCriteria
{
    public const MAX_SEARCH_LENGTH = 100;

    private function __construct(
        public ?string $search,
        public ProductSort $sort,
    ) {
    }

    public static function default(): self
    {
        return new self(null, ProductSort::newest());
    }

    public static function fromInput(?string $search, ?string $sort): self
    {
        return new self(self::normalizeSearch($search), ProductSort::fromString($sort));
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
