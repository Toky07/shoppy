<?php

declare(strict_types=1);

namespace App\Product\Application\Query;

final readonly class ListProductReviewsQuery
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_LIMIT = 20;
    public const MAX_LIMIT = 100;

    public function __construct(
        public string $productId,
        public ?string $viewerId = null,
        public int $page = self::DEFAULT_PAGE,
        public int $limit = self::DEFAULT_LIMIT,
    ) {
    }
}
