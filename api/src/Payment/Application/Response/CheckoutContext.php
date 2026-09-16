<?php

declare(strict_types=1);

namespace App\Payment\Application\Response;

final readonly class CheckoutContext
{
    public function __construct(
        public string $successUrl,
        public string $cancelUrl,
    ) {
    }
}
