<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Persistence\Doctrine;

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\Repository\InvoiceRepository;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Infrastructure\Persistence\Doctrine\Entity\InvoiceRecord;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineInvoiceRepository implements InvoiceRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Invoice $invoice): void
    {
        $record = $this->findRecordByOrderId($invoice->orderId()->value());

        if ($record === null) {
            $this->entityManager->persist(InvoiceRecord::fromDomain($invoice));
        } else {
            $record->updateFromDomain($invoice);
        }

        $this->entityManager->flush();
    }

    public function findByOrderId(OrderReference $orderId): ?Invoice
    {
        return $this->findRecordByOrderId($orderId->value())?->toDomain();
    }

    public function nextNumber(DateTimeImmutable $issuedAt): InvoiceNumber
    {
        $year = (int) $issuedAt->format('Y');
        $maxSequence = $this->entityManager->createQueryBuilder()
            ->select('MAX(i.sequence)')
            ->from(InvoiceRecord::class, 'i')
            ->where('i.year = :year')
            ->setParameter('year', $year)
            ->getQuery()
            ->getSingleScalarResult();

        return InvoiceNumber::of($year, ((int) $maxSequence) + 1);
    }

    private function findRecordByOrderId(string $orderId): ?InvoiceRecord
    {
        $record = $this->entityManager->createQueryBuilder()
            ->select('i', 'items')
            ->from(InvoiceRecord::class, 'i')
            ->leftJoin('i.items', 'items')
            ->where('i.orderId = :orderId')
            ->setParameter('orderId', $orderId)
            ->getQuery()
            ->getOneOrNullResult();

        return $record instanceof InvoiceRecord ? $record : null;
    }
}
