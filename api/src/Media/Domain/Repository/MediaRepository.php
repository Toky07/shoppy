<?php

declare(strict_types=1);

namespace App\Media\Domain\Repository;

use App\Media\Domain\Entity\Media;
use App\Media\Domain\ValueObject\MediaId;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;

interface MediaRepository
{
    public function save(Media $media): void;

    public function findById(MediaId $id): ?Media;

    /**
     * @return list<Media>
     */
    public function findByOwner(MediaOwnerType $ownerType, MediaOwnerId $ownerId): array;

    /**
     * @param list<MediaOwnerId> $ownerIds
     *
     * @return array<string, list<Media>>
     */
    public function findByOwners(MediaOwnerType $ownerType, array $ownerIds): array;

    public function delete(Media $media): void;
}
