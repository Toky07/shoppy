<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Persistence\Doctrine\Entity;

use App\Media\Domain\Entity\Media;
use App\Media\Domain\ValueObject\MediaFilename;
use App\Media\Domain\ValueObject\MediaId;
use App\Media\Domain\ValueObject\MediaMimeType;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Domain\ValueObject\MediaPosition;
use App\Media\Domain\ValueObject\MediaRelativePath;
use App\Media\Domain\ValueObject\MediaSize;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'media')]
#[ORM\Index(name: 'idx_media_owner', columns: ['owner_type', 'owner_id'])]
class MediaRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(name: 'owner_type', length: 255)]
    private string $ownerType;

    #[ORM\Column(name: 'owner_id', length: 36)]
    private string $ownerId;

    #[ORM\Column(length: 255)]
    private string $filename;

    #[ORM\Column(name: 'relative_path', length: 512)]
    private string $relativePath;

    #[ORM\Column(name: 'mime_type', length: 100)]
    private string $mimeType;

    #[ORM\Column]
    private int $size;

    #[ORM\Column]
    private int $position;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    public static function fromDomain(Media $media): self
    {
        $record = new self();
        $record->apply($media);

        return $record;
    }

    public function updateFromDomain(Media $media): void
    {
        $this->apply($media);
    }

    public function toDomain(): Media
    {
        return Media::create(
            MediaId::fromString($this->id),
            MediaOwnerType::fromInput($this->ownerType),
            MediaOwnerId::fromString($this->ownerId),
            MediaFilename::fromString($this->filename),
            MediaRelativePath::fromString($this->relativePath),
            MediaMimeType::fromString($this->mimeType),
            MediaSize::fromInt($this->size),
            MediaPosition::fromInt($this->position),
            $this->createdAt,
        );
    }

    private function apply(Media $media): void
    {
        $this->id = $media->id()->value();
        $this->ownerType = $media->ownerType()->className();
        $this->ownerId = $media->ownerId()->value();
        $this->filename = $media->filename()->value();
        $this->relativePath = $media->relativePath()->value();
        $this->mimeType = $media->mimeType()->value();
        $this->size = $media->size()->value();
        $this->position = $media->position()->value();
        $this->createdAt = $media->createdAt();
    }
}
