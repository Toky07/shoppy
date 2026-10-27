<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Persistence\Doctrine;

use App\Invoice\Domain\Entity\InvoiceOrderSnapshot;
use App\Invoice\Domain\Repository\InvoiceOrderSnapshotRepository;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrderSnapshotRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineInvoiceOrderSnapshotRepository implements InvoiceOrderSnapshotRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(InvoiceOrderSnapshot $snapshot): void
    {
        $record = $this->findRecord($snapshot->orderId()->value());

        if ($record === null) {
            $this->entityManager->persist(InvoiceOrderSnapshotRecord::fromDomain($snapshot));
        } else {
            $record->replaceFromDomain($snapshot);
        }

        $this->entityManager->flush();
    }

    public function findByOrderId(OrderReference $orderId): ?InvoiceOrderSnapshot
    {
        return $this->findRecord($orderId->value())?->toDomain();
    }

    private function findRecord(string $orderId): ?InvoiceOrderSnapshotRecord
    {
        $record = $this->entityManager->createQueryBuilder()
            ->select('s', 'items')
            ->from(InvoiceOrderSnapshotRecord::class, 's')
            ->leftJoin('s.items', 'items')
            ->where('s.orderId = :orderId')
            ->setParameter('orderId', $orderId)
            ->getQuery()
            ->getOneOrNullResult();

        return $record instanceof InvoiceOrderSnapshotRecord ? $record : null;
    }
}
