<?php

declare(strict_types=1);

namespace App\Audit\Infrastructure\Persistence\Doctrine\Entity;

use App\Audit\Domain\Entity\AuditEntry;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditId;
use App\Audit\Domain\ValueObject\AuditSubjectType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audit_log')]
#[ORM\Index(name: 'idx_audit_log_created_at', columns: ['created_at'])]
class AuditLogRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 32)]
    private string $action;

    #[ORM\Column(name: 'actor_id', length: 36, nullable: true)]
    private ?string $actorId = null;

    #[ORM\Column(name: 'actor_email', length: 255, nullable: true)]
    private ?string $actorEmail = null;

    #[ORM\Column(name: 'subject_type', length: 32)]
    private string $subjectType;

    #[ORM\Column(name: 'subject_id', length: 36)]
    private string $subjectId;

    /** @var array<string, scalar|null> */
    #[ORM\Column(type: Types::JSON)]
    private array $metadata = [];

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    public static function fromDomain(AuditEntry $entry): self
    {
        $record = new self();
        $record->id = $entry->id()->value();
        $record->action = $entry->action()->value();
        $record->actorId = $entry->actorId();
        $record->actorEmail = $entry->actorEmail();
        $record->subjectType = $entry->subjectType()->value();
        $record->subjectId = $entry->subjectId();
        $record->metadata = $entry->metadata();
        $record->createdAt = $entry->createdAt();

        return $record;
    }

    public function toDomain(): AuditEntry
    {
        return AuditEntry::record(
            AuditId::fromString($this->id),
            AuditAction::fromString($this->action),
            $this->actorId,
            $this->actorEmail,
            AuditSubjectType::fromString($this->subjectType),
            $this->subjectId,
            $this->metadata,
            $this->createdAt,
        );
    }
}
