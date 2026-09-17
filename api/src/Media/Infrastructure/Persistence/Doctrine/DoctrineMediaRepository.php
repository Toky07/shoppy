<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Persistence\Doctrine;

use App\Media\Domain\Entity\Media;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaId;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Infrastructure\Persistence\Doctrine\Entity\MediaRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineMediaRepository implements MediaRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Media $media): void
    {
        $record = $this->entityManager->find(MediaRecord::class, $media->id()->value());

        if ($record === null) {
            $this->entityManager->persist(MediaRecord::fromDomain($media));
        } else {
            $record->updateFromDomain($media);
        }

        $this->entityManager->flush();
    }

    public function findById(MediaId $id): ?Media
    {
        return $this->entityManager->find(MediaRecord::class, $id->value())?->toDomain();
    }

    public function findByOwner(MediaOwnerType $ownerType, MediaOwnerId $ownerId): array
    {
        $records = $this->entityManager->createQueryBuilder()
            ->select('media')
            ->from(MediaRecord::class, 'media')
            ->where('media.ownerType = :ownerType')
            ->andWhere('media.ownerId = :ownerId')
            ->setParameter('ownerType', $ownerType->className())
            ->setParameter('ownerId', $ownerId->value())
            ->orderBy('media.position', 'ASC')
            ->addOrderBy('media.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (MediaRecord $record): Media => $record->toDomain(),
            $records,
        );
    }

    public function delete(Media $media): void
    {
        $record = $this->entityManager->find(MediaRecord::class, $media->id()->value());

        if ($record === null) {
            return;
        }

        $this->entityManager->remove($record);
        $this->entityManager->flush();
    }
}
