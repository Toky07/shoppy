<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Persistence;

use App\Invoice\Domain\Entity\InvoiceOrderSnapshot;
use App\Invoice\Domain\Repository\InvoiceOrderSnapshotRepository;
use App\Invoice\Domain\ValueObject\OrderReference;

final class InMemoryInvoiceOrderSnapshotRepository implements InvoiceOrderSnapshotRepository
{
    /** @var array<string, InvoiceOrderSnapshot> */
    private array $byOrderId = [];

    public function save(InvoiceOrderSnapshot $snapshot): void
    {
        $this->byOrderId[$snapshot->orderId()->value()] = $snapshot;
    }

    public function findByOrderId(OrderReference $orderId): ?InvoiceOrderSnapshot
    {
        return $this->byOrderId[$orderId->value()] ?? null;
    }
}
