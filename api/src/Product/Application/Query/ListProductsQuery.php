<?php

declare(strict_types=1);

namespace App\Product\Application\Query;

final readonly class ListProductsQuery
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_LIMIT = 20;
    public const MAX_LIMIT = 100;

    public function __construct(
        public int $page = self::DEFAULT_PAGE,
        public int $limit = self::DEFAULT_LIMIT,
        public ?string $search = null,
        public ?string $sort = null,
        public ?int $minPriceCents = null,
        public ?int $maxPriceCents = null,
        public bool $inStockOnly = false,
        public ?string $categorySlug = null,
    ) {
    }
}
