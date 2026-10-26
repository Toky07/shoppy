<?php

declare(strict_types=1);

namespace App\Media\Application\CommandHandler;

use App\Media\Application\Command\DeleteMediaByOwnerCommand;
use App\Media\Application\Port\ImageVariants;
use App\Media\Application\Port\MediaStorage;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;

final readonly class DeleteMediaByOwnerCommandHandler
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private MediaStorage $mediaStorage,
        private ImageVariants $imageVariants,
    ) {
    }

    public function handle(DeleteMediaByOwnerCommand $command): void
    {
        $ownerType = MediaOwnerType::fromInput($command->ownerType);
        $ownerId = MediaOwnerId::fromString($command->ownerId);

        foreach ($this->mediaRepository->findByOwner($ownerType, $ownerId) as $media) {
            $path = $media->relativePath()->value();
            $this->imageVariants->delete($path);
            $this->mediaStorage->delete($path);
            $this->mediaRepository->delete($media);
        }
    }
}
