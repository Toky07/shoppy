<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Audit\Application\Port\AuditLogger;

final class InMemoryAuditLogger implements AuditLogger
{
    /** @var list<array{action: string, subjectType: string, subjectId: string, metadata: array<string, scalar|null>}> */
    public array $entries = [];

    public function record(string $action, string $subjectType, string $subjectId, array $metadata = []): void
    {
        $this->entries[] = [
            'action' => $action,
            'subjectType' => $subjectType,
            'subjectId' => $subjectId,
            'metadata' => $metadata,
        ];
    }
}
