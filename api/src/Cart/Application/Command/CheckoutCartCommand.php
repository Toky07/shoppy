<?php

declare(strict_types=1);

namespace App\Cart\Application\Command;

use App\Order\Application\Command\PlaceOrderAddress;

final readonly class CheckoutCartCommand
{
    public function __construct(
        public string $customerId,
        public PlaceOrderAddress $shippingAddress,
        public PlaceOrderAddress $billingAddress,
        public string $shippingMethod,
    ) {
    }
}
