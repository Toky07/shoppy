<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

final readonly class PaymentCompleted
{
    public function __construct(
        public string $paymentId,
        public string $orderId,
        public string $customerId,
        public int $amountCents,
    ) {
    }
}
