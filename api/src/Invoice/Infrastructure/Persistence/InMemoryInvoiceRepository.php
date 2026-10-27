<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Persistence;

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\Repository\InvoiceRepository;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\OrderReference;
use DateTimeImmutable;

final class InMemoryInvoiceRepository implements InvoiceRepository
{
    /** @var array<string, Invoice> */
    private array $byOrderId = [];

    public function save(Invoice $invoice): void
    {
        $this->byOrderId[$invoice->orderId()->value()] = $invoice;
    }

    public function findByOrderId(OrderReference $orderId): ?Invoice
    {
        return $this->byOrderId[$orderId->value()] ?? null;
    }

    public function nextNumber(DateTimeImmutable $issuedAt): InvoiceNumber
    {
        $year = (int) $issuedAt->format('Y');
        $sequence = 0;

        foreach ($this->byOrderId as $invoice) {
            if ($invoice->number()->year() === $year) {
                $sequence = max($sequence, $invoice->number()->sequence());
            }
        }

        return InvoiceNumber::of($year, $sequence + 1);
    }

    /**
     * @return list<Invoice>
     */
    public function all(): array
    {
        return array_values($this->byOrderId);
    }
}
