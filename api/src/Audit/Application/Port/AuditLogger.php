<?php

declare(strict_types=1);

namespace App\Audit\Application\Port;

interface AuditLogger
{
    /**
     * @param array<string, scalar|null> $metadata
     */
    public function record(string $action, string $subjectType, string $subjectId, array $metadata = []): void;
}
