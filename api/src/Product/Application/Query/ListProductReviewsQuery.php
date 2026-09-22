<?php

declare(strict_types=1);

namespace App\Product\Application\Query;

final readonly class ListProductReviewsQuery
{
    public function __construct(
        public string $productId,
        public ?string $viewerId = null,
    ) {
    }
}
