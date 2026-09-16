<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Gateway\Stripe;

final readonly class CreatedStripeSession
{
    public function __construct(
        public string $id,
        public string $url,
    ) {
    }
}
