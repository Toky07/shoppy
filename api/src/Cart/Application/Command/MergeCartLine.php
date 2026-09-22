<?php

declare(strict_types=1);

namespace App\Cart\Application\Command;

final readonly class MergeCartLine
{
    public function __construct(
        public string $productId,
        public int $quantity,
        public ?string $variantId = null,
    ) {
    }
}
