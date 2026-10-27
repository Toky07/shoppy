<?php

declare(strict_types=1);

namespace App\Favorite\Application\Command;

final readonly class MergeFavoritesCommand
{
    /**
     * @param list<string> $productIds
     */
    public function __construct(
        public string $customerId,
        public array $productIds,
    ) {
    }
}
