<?php

declare(strict_types=1);

namespace App\Order\Application\Command;

final readonly class PlaceOrderCommand
{
    /**
     * @param list<PlaceOrderLine> $items
     */
    public function __construct(
        public string $customerId,
        public array $items,
        public PlaceOrderAddress $shippingAddress,
        public PlaceOrderAddress $billingAddress,
    ) {
    }
}
