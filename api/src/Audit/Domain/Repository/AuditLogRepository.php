<?php

declare(strict_types=1);

namespace App\Audit\Domain\Repository;

use App\Audit\Domain\Entity\AuditEntry;

interface AuditLogRepository
{
    public function save(AuditEntry $entry): void;

    /**
     * @return list<AuditEntry>
     */
    public function findPage(int $offset, int $limit): array;

    public function countAll(): int;
}
