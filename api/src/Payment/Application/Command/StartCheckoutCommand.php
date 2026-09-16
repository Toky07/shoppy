<?php

declare(strict_types=1);

namespace App\Payment\Application\Command;

final readonly class StartCheckoutCommand
{
    public function __construct(
        public string $orderId,
        public string $customerId,
        public string $provider,
        public string $successUrl,
        public string $cancelUrl,
    ) {
    }
}
