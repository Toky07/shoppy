<?php

declare(strict_types=1);

namespace App\Audit\Application;

use App\Audit\Application\Port\AuditLogger;
use App\Audit\Application\Port\CurrentActor;
use App\Audit\Domain\Entity\AuditEntry;
use App\Audit\Domain\Repository\AuditLogRepository;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditId;
use App\Audit\Domain\ValueObject\AuditSubjectType;
use App\Shared\Domain\Clock;

final readonly class RecordingAuditLogger implements AuditLogger
{
    public function __construct(
        private AuditLogRepository $auditLog,
        private CurrentActor $actor,
        private Clock $clock,
    ) {
    }

    public function record(string $action, string $subjectType, string $subjectId, array $metadata = []): void
    {
        $this->auditLog->save(AuditEntry::record(
            AuditId::generate(),
            AuditAction::fromString($action),
            $this->actor->id(),
            $this->actor->email(),
            AuditSubjectType::fromString($subjectType),
            $subjectId,
            $metadata,
            $this->clock->now(),
        ));
    }
}
