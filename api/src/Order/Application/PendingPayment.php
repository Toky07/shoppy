<?php

declare(strict_types=1);

namespace App\Order\Application;

interface PendingPayment
{
    public function open(string $orderId, string $customerId, int $amountCents): void;
}
