<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Persistence;

use App\Media\Domain\Entity\Media;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaId;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;

final class InMemoryMediaRepository implements MediaRepository
{
    /** @var array<string, Media> */
    private array $items = [];

    public function save(Media $media): void
    {
        $this->items[$media->id()->value()] = $media;
    }

    public function findById(MediaId $id): ?Media
    {
        return $this->items[$id->value()] ?? null;
    }

    public function findByOwner(MediaOwnerType $ownerType, MediaOwnerId $ownerId): array
    {
        $matches = array_values(array_filter(
            $this->items,
            static fn (Media $media): bool => $media->ownerType()->className() === $ownerType->className()
                && $media->ownerId()->value() === $ownerId->value(),
        ));

        usort(
            $matches,
            static function (Media $left, Media $right): int {
                $byPosition = $left->position()->value() <=> $right->position()->value();

                if ($byPosition !== 0) {
                    return $byPosition;
                }

                return $left->createdAt() <=> $right->createdAt();
            },
        );

        return $matches;
    }

    public function delete(Media $media): void
    {
        unset($this->items[$media->id()->value()]);
    }
}
