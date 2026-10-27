<?php

declare(strict_types=1);

namespace App\Product\Application\Command;

final readonly class ReportProductReviewCommand
{
    public function __construct(
        public string $productId,
        public string $reviewId,
        public string $reporterId,
    ) {
    }
}
