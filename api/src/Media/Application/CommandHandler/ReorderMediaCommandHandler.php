<?php

declare(strict_types=1);

namespace App\Media\Application\CommandHandler;

use App\Media\Application\Command\ReorderMediaCommand;
use App\Media\Domain\Exception\InvalidMediaOrder;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Domain\ValueObject\MediaPosition;

final readonly class ReorderMediaCommandHandler
{
    public function __construct(private MediaRepository $mediaRepository)
    {
    }

    public function handle(ReorderMediaCommand $command): void
    {
        $ownerType = MediaOwnerType::fromInput($command->ownerType);
        $ownerId = MediaOwnerId::fromString($command->ownerId);
        $existing = $this->mediaRepository->findByOwner($ownerType, $ownerId);
        $byId = [];

        foreach ($existing as $media) {
            $byId[$media->id()->value()] = $media;
        }

        if (count($command->ids) !== count($byId)) {
            throw new InvalidMediaOrder();
        }

        $seen = [];

        foreach ($command->ids as $position => $id) {
            if (!is_string($id) || !isset($byId[$id]) || isset($seen[$id])) {
                throw new InvalidMediaOrder();
            }

            $seen[$id] = true;
            $byId[$id]->moveTo(MediaPosition::fromInt($position));
            $this->mediaRepository->save($byId[$id]);
        }
    }
}
