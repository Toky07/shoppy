<?php

declare(strict_types=1);

namespace App\Audit\Infrastructure\Persistence;

use App\Audit\Domain\Entity\AuditEntry;
use App\Audit\Domain\Repository\AuditLogRepository;

final class InMemoryAuditLogRepository implements AuditLogRepository
{
    /** @var array<string, AuditEntry> */
    private array $entries = [];

    public function save(AuditEntry $entry): void
    {
        $this->entries[$entry->id()->value()] = $entry;
    }

    public function findPage(int $offset, int $limit): array
    {
        $entries = array_values($this->entries);
        usort(
            $entries,
            static fn (AuditEntry $left, AuditEntry $right): int => [$right->createdAt(), $right->id()->value()]
                <=> [$left->createdAt(), $left->id()->value()],
        );

        return array_values(array_slice($entries, $offset, $limit));
    }

    public function countAll(): int
    {
        return count($this->entries);
    }
}
