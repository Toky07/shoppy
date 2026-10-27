<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Repository;

use App\Invoice\Domain\Entity\InvoiceOrderSnapshot;
use App\Invoice\Domain\ValueObject\OrderReference;

interface InvoiceOrderSnapshotRepository
{
    public function save(InvoiceOrderSnapshot $snapshot): void;

    public function findByOrderId(OrderReference $orderId): ?InvoiceOrderSnapshot;
}
