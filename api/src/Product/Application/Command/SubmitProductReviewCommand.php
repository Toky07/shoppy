<?php

declare(strict_types=1);

namespace App\Product\Application\Command;

final readonly class SubmitProductReviewCommand
{
    public function __construct(
        public string $productId,
        public string $authorId,
        public int $rating,
        public string $body,
    ) {
    }
}
