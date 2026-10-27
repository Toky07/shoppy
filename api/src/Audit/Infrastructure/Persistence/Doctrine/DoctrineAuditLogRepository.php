<?php

declare(strict_types=1);

namespace App\Audit\Infrastructure\Persistence\Doctrine;

use App\Audit\Domain\Entity\AuditEntry;
use App\Audit\Domain\Repository\AuditLogRepository;
use App\Audit\Infrastructure\Persistence\Doctrine\Entity\AuditLogRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAuditLogRepository implements AuditLogRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(AuditEntry $entry): void
    {
        $this->entityManager->persist(AuditLogRecord::fromDomain($entry));
        $this->entityManager->flush();
    }

    public function findPage(int $offset, int $limit): array
    {
        /** @var list<AuditLogRecord> $records */
        $records = $this->entityManager->createQueryBuilder()
            ->select('entry')
            ->from(AuditLogRecord::class, 'entry')
            ->orderBy('entry.createdAt', 'DESC')
            ->addOrderBy('entry.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (AuditLogRecord $record): AuditEntry => $record->toDomain(),
            $records,
        );
    }

    public function countAll(): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(entry.id)')
            ->from(AuditLogRecord::class, 'entry')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
