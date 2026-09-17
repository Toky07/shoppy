<?php

declare(strict_types=1);

namespace App\Media\Application\CommandHandler;

use App\Media\Application\Command\DeleteMediaCommand;
use App\Media\Application\Port\MediaStorage;
use App\Media\Domain\Exception\MediaNotFound;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaId;

final readonly class DeleteMediaCommandHandler
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private MediaStorage $mediaStorage,
    ) {
    }

    public function handle(DeleteMediaCommand $command): void
    {
        $id = MediaId::fromString($command->id);
        $media = $this->mediaRepository->findById($id);

        if ($media === null) {
            throw new MediaNotFound($id);
        }

        $this->mediaStorage->delete($media->relativePath()->value());
        $this->mediaRepository->delete($media);
    }
}
