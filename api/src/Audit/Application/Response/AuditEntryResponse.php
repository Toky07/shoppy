<?php

declare(strict_types=1);

namespace App\Audit\Application\Response;

use App\Audit\Domain\Entity\AuditEntry;
use DateTimeInterface;

final readonly class AuditEntryResponse
{
    /**
     * @param array<string, scalar|null> $metadata
     */
    public function __construct(
        public string $id,
        public string $action,
        public ?string $actorId,
        public ?string $actorEmail,
        public string $subjectType,
        public string $subjectId,
        public array $metadata,
        public string $createdAt,
    ) {
    }

    public static function fromEntry(AuditEntry $entry): self
    {
        return new self(
            $entry->id()->value(),
            $entry->action()->value(),
            $entry->actorId(),
            $entry->actorEmail(),
            $entry->subjectType()->value(),
            $entry->subjectId(),
            $entry->metadata(),
            $entry->createdAt()->format(DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     action: string,
     *     actorId: string|null,
     *     actorEmail: string|null,
     *     subjectType: string,
     *     subjectId: string,
     *     metadata: array<string, scalar|null>,
     *     createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'actorId' => $this->actorId,
            'actorEmail' => $this->actorEmail,
            'subjectType' => $this->subjectType,
            'subjectId' => $this->subjectId,
            'metadata' => $this->metadata,
            'createdAt' => $this->createdAt,
        ];
    }
}
