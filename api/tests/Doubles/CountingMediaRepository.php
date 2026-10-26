<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Media\Domain\Entity\Media;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaId;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;

final class CountingMediaRepository implements MediaRepository
{
    public int $findByOwnerCalls = 0;

    public int $findByOwnersCalls = 0;

    public function __construct(private MediaRepository $inner)
    {
    }

    public function save(Media $media): void
    {
        $this->inner->save($media);
    }

    public function findById(MediaId $id): ?Media
    {
        return $this->inner->findById($id);
    }

    public function findByOwner(MediaOwnerType $ownerType, MediaOwnerId $ownerId): array
    {
        $this->findByOwnerCalls++;

        return $this->inner->findByOwner($ownerType, $ownerId);
    }

    public function findByOwners(MediaOwnerType $ownerType, array $ownerIds): array
    {
        $this->findByOwnersCalls++;

        return $this->inner->findByOwners($ownerType, $ownerIds);
    }

    public function delete(Media $media): void
    {
        $this->inner->delete($media);
    }
}
