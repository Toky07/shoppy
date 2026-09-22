<?php

declare(strict_types=1);

namespace App\Media\Domain\Entity;

use App\Media\Domain\ValueObject\MediaFilename;
use App\Media\Domain\ValueObject\MediaId;
use App\Media\Domain\ValueObject\MediaMimeType;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Domain\ValueObject\MediaPosition;
use App\Media\Domain\ValueObject\MediaRelativePath;
use App\Media\Domain\ValueObject\MediaSize;
use DateTimeImmutable;

final class Media
{
    private function __construct(
        private MediaId $id,
        private MediaOwnerType $ownerType,
        private MediaOwnerId $ownerId,
        private MediaFilename $filename,
        private MediaRelativePath $relativePath,
        private MediaMimeType $mimeType,
        private MediaSize $size,
        private MediaPosition $position,
        private DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        MediaId $id,
        MediaOwnerType $ownerType,
        MediaOwnerId $ownerId,
        MediaFilename $filename,
        MediaRelativePath $relativePath,
        MediaMimeType $mimeType,
        MediaSize $size,
        MediaPosition $position,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $ownerType,
            $ownerId,
            $filename,
            $relativePath,
            $mimeType,
            $size,
            $position,
            $createdAt,
        );
    }

    public function id(): MediaId
    {
        return $this->id;
    }

    public function ownerType(): MediaOwnerType
    {
        return $this->ownerType;
    }

    public function ownerId(): MediaOwnerId
    {
        return $this->ownerId;
    }

    public function filename(): MediaFilename
    {
        return $this->filename;
    }

    public function relativePath(): MediaRelativePath
    {
        return $this->relativePath;
    }

    public function publicUrl(): string
    {
        return $this->relativePath->publicUrl();
    }

    public function mimeType(): MediaMimeType
    {
        return $this->mimeType;
    }

    public function size(): MediaSize
    {
        return $this->size;
    }

    public function position(): MediaPosition
    {
        return $this->position;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function moveTo(MediaPosition $position): void
    {
        $this->position = $position;
    }
}
