<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Repository;

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\OrderReference;
use DateTimeImmutable;

interface InvoiceRepository
{
    public function save(Invoice $invoice): void;

    public function findByOrderId(OrderReference $orderId): ?Invoice;

    public function nextNumber(DateTimeImmutable $issuedAt): InvoiceNumber;
}
