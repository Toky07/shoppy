<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

final readonly class OrderPlaced
{
    /**
     * @param list<OrderPlacedItem> $items
     */
    public function __construct(
        public string $orderId,
        public string $customerId,
        public int $amountCents,
        public array $items = [],
        public ?string $shippingLabel = null,
        public int $shippingCents = 0,
    ) {
    }
}
