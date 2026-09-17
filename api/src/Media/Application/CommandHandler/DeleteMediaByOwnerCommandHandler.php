<?php

declare(strict_types=1);

namespace App\Media\Application\CommandHandler;

use App\Media\Application\Command\DeleteMediaByOwnerCommand;
use App\Media\Application\Port\MediaStorage;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;

final readonly class DeleteMediaByOwnerCommandHandler
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private MediaStorage $mediaStorage,
    ) {
    }

    public function handle(DeleteMediaByOwnerCommand $command): void
    {
        $ownerType = MediaOwnerType::fromInput($command->ownerType);
        $ownerId = MediaOwnerId::fromString($command->ownerId);

        foreach ($this->mediaRepository->findByOwner($ownerType, $ownerId) as $media) {
            $this->mediaStorage->delete($media->relativePath()->value());
            $this->mediaRepository->delete($media);
        }
    }
}
