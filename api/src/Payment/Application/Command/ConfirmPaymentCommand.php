<?php

declare(strict_types=1);

namespace App\Payment\Application\Command;

final readonly class ConfirmPaymentCommand
{
    public function __construct(
        public string $orderId,
        public string $customerId,
    ) {
    }
}
