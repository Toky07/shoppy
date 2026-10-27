<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Entity;

use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\OrderReference;

final readonly class InvoiceOrderSnapshot
{
    /**
     * @param list<InvoiceLine> $lines
     */
    public function __construct(
        private OrderReference $orderId,
        private CustomerReference $customerId,
        private array $lines,
    ) {
    }

    public function orderId(): OrderReference
    {
        return $this->orderId;
    }

    public function customerId(): CustomerReference
    {
        return $this->customerId;
    }

    /**
     * @return list<InvoiceLine>
     */
    public function lines(): array
    {
        return $this->lines;
    }
}
