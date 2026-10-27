<?php

declare(strict_types=1);

namespace App\Product\Application\Command;

final readonly class HideProductReviewCommand
{
    public function __construct(public string $reviewId)
    {
    }
}
