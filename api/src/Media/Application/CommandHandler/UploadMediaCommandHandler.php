<?php

declare(strict_types=1);

namespace App\Media\Application\CommandHandler;

use App\Media\Application\Command\UploadMediaCommand;
use App\Media\Application\Port\MediaStorage;
use App\Media\Domain\Entity\Media;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaFilename;
use App\Media\Domain\ValueObject\MediaId;
use App\Media\Domain\ValueObject\MediaMimeType;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Domain\ValueObject\MediaPosition;
use App\Media\Domain\ValueObject\MediaRelativePath;
use App\Media\Domain\ValueObject\MediaSize;
use App\Shared\Domain\Clock;

final readonly class UploadMediaCommandHandler
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private MediaStorage $mediaStorage,
        private Clock $clock,
    ) {
    }

    public function handle(UploadMediaCommand $command): MediaId
    {
        $ownerType = MediaOwnerType::fromInput($command->ownerType);
        $ownerId = MediaOwnerId::fromString($command->ownerId);
        $mimeType = MediaMimeType::fromString($command->mimeType);
        $size = MediaSize::fromInt(strlen($command->binaryContent));
        $stored = $this->mediaStorage->store(
            $command->originalFilename,
            $mimeType->extension(),
            $command->binaryContent,
            $this->clock->now(),
        );

        $media = Media::create(
            MediaId::generate(),
            $ownerType,
            $ownerId,
            MediaFilename::fromString($stored->filename),
            MediaRelativePath::fromString($stored->relativePath),
            $mimeType,
            $size,
            $this->positionFor($command->position, $ownerType, $ownerId),
            $this->clock->now(),
        );

        $this->mediaRepository->save($media);

        return $media->id();
    }

    private function positionFor(?int $position, MediaOwnerType $ownerType, MediaOwnerId $ownerId): MediaPosition
    {
        if ($position !== null) {
            return MediaPosition::fromInt($position);
        }

        $existing = $this->mediaRepository->findByOwner($ownerType, $ownerId);

        return MediaPosition::fromInt(count($existing));
    }
}
