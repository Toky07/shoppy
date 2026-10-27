<?php

declare(strict_types=1);

namespace App\Favorite\Infrastructure\Persistence\Doctrine;

use App\Favorite\Domain\Entity\FavoriteList;
use App\Favorite\Domain\Repository\FavoriteListRepository;
use App\Favorite\Domain\ValueObject\CustomerId;
use App\Favorite\Infrastructure\Persistence\Doctrine\Entity\FavoriteListRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineFavoriteListRepository implements FavoriteListRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(FavoriteList $list): void
    {
        $record = $this->entityManager->find(FavoriteListRecord::class, $list->customerId()->value());

        if ($record === null) {
            $this->entityManager->persist(FavoriteListRecord::fromDomain($list));
        } else {
            $record->updateFromDomain($list);
        }

        $this->entityManager->flush();
    }

    public function findByCustomerId(CustomerId $customerId): ?FavoriteList
    {
        $record = $this->entityManager->find(FavoriteListRecord::class, $customerId->value());

        return $record?->toDomain();
    }
}
