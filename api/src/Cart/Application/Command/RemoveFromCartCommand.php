<?php

declare(strict_types=1);

namespace App\Cart\Application\Command;

final readonly class RemoveFromCartCommand
{
    public function __construct(
        public string $customerId,
        public string $productId,
    ) {
    }
}
