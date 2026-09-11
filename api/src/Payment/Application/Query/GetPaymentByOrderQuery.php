<?php

declare(strict_types=1);

namespace App\Payment\Application\Query;

final readonly class GetPaymentByOrderQuery
{
    public function __construct(
        public string $orderId,
        public string $customerId,
    ) {
    }
}
