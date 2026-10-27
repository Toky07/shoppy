<?php

declare(strict_types=1);

namespace App\Audit\Domain\Entity;

use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditId;
use App\Audit\Domain\ValueObject\AuditSubjectType;
use DateTimeImmutable;

final readonly class AuditEntry
{
    /**
     * @param array<string, scalar|null> $metadata
     */
    private function __construct(
        private AuditId $id,
        private AuditAction $action,
        private ?string $actorId,
        private ?string $actorEmail,
        private AuditSubjectType $subjectType,
        private string $subjectId,
        private array $metadata,
        private DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * @param array<string, scalar|null> $metadata
     */
    public static function record(
        AuditId $id,
        AuditAction $action,
        ?string $actorId,
        ?string $actorEmail,
        AuditSubjectType $subjectType,
        string $subjectId,
        array $metadata,
        DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $action, $actorId, $actorEmail, $subjectType, $subjectId, $metadata, $createdAt);
    }

    public function id(): AuditId
    {
        return $this->id;
    }

    public function action(): AuditAction
    {
        return $this->action;
    }

    public function actorId(): ?string
    {
        return $this->actorId;
    }

    public function actorEmail(): ?string
    {
        return $this->actorEmail;
    }

    public function subjectType(): AuditSubjectType
    {
        return $this->subjectType;
    }

    public function subjectId(): string
    {
        return $this->subjectId;
    }

    /**
     * @return array<string, scalar|null>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
