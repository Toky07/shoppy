<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Gateway\Stripe;

final readonly class RetrievedStripeSession
{
    public function __construct(
        public string $id,
        public bool $paid,
        public ?int $amountCents,
    ) {
    }
}
