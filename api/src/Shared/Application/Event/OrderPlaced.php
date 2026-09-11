<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

final readonly class OrderPlaced
{
    public function __construct(
        public string $orderId,
        public string $customerId,
        public int $amountCents,
    ) {
    }
}
