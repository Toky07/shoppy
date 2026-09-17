<?php

declare(strict_types=1);

namespace App\Media\Application\Response;

use App\Media\Domain\Entity\Media;
use DateTimeInterface;

final readonly class MediaResponse
{
    public function __construct(
        public string $id,
        public string $ownerType,
        public string $ownerId,
        public string $url,
        public string $filename,
        public string $mimeType,
        public int $size,
        public int $position,
        public string $createdAt,
    ) {
    }

    public static function fromMedia(Media $media): self
    {
        return new self(
            $media->id()->value(),
            $media->ownerType()->alias(),
            $media->ownerId()->value(),
            $media->publicUrl(),
            $media->filename()->value(),
            $media->mimeType()->value(),
            $media->size()->value(),
            $media->position()->value(),
            $media->createdAt()->format(DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     ownerType: string,
     *     ownerId: string,
     *     url: string,
     *     filename: string,
     *     mimeType: string,
     *     size: int,
     *     position: int,
     *     createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ownerType' => $this->ownerType,
            'ownerId' => $this->ownerId,
            'url' => $this->url,
            'filename' => $this->filename,
            'mimeType' => $this->mimeType,
            'size' => $this->size,
            'position' => $this->position,
            'createdAt' => $this->createdAt,
        ];
    }
}
