<?php

declare(strict_types=1);

namespace App\Media\Application\CommandHandler;

use App\Media\Application\Command\DeleteMediaCommand;
use App\Media\Application\Port\ImageVariants;
use App\Media\Application\Port\MediaStorage;
use App\Media\Domain\Exception\MediaNotFound;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaId;

final readonly class DeleteMediaCommandHandler
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private MediaStorage $mediaStorage,
        private ImageVariants $imageVariants,
    ) {
    }

    public function handle(DeleteMediaCommand $command): void
    {
        $id = MediaId::fromString($command->id);
        $media = $this->mediaRepository->findById($id);

        if ($media === null) {
            throw new MediaNotFound($id);
        }

        $path = $media->relativePath()->value();
        $this->imageVariants->delete($path);
        $this->mediaStorage->delete($path);
        $this->mediaRepository->delete($media);
    }
}
