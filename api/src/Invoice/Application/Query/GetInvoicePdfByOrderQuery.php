<?php

declare(strict_types=1);

namespace App\Invoice\Application\Query;

final readonly class GetInvoicePdfByOrderQuery
{
    public function __construct(public string $orderId)
    {
    }
}
