<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

final readonly class InvoiceIssued
{
    public function __construct(
        public string $invoiceId,
        public string $number,
        public string $orderId,
        public string $customerId,
        public string $paymentId,
        public int $amountCents,
    ) {
    }
}
