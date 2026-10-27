<?php

declare(strict_types=1);

namespace App\Cart\Application\Command;

use App\Order\Application\Command\PlaceOrderAddress;

final readonly class GuestCheckoutCommand
{
    /**
     * @param list<GuestCheckoutLine> $items
     */
    public function __construct(
        public string $email,
        public array $items,
        public PlaceOrderAddress $shippingAddress,
        public PlaceOrderAddress $billingAddress,
        public string $shippingMethod,
    ) {
    }
}
