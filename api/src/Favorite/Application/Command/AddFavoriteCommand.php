<?php

declare(strict_types=1);

namespace App\Favorite\Application\Command;

final readonly class AddFavoriteCommand
{
    public function __construct(
        public string $customerId,
        public string $productId,
    ) {
    }
}
