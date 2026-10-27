<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

final readonly class OrderPlacedItem
{
    public function __construct(
        public string $description,
        public int $quantity,
        public int $unitPriceCents,
    ) {
    }
}
