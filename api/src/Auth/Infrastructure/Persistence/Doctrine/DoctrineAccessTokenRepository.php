<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Doctrine;

use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;
use App\Auth\Infrastructure\Persistence\Doctrine\Entity\AccessTokenRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAccessTokenRepository implements AccessTokenRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(AccessToken $token): void
    {
        $record = $this->entityManager->find(AccessTokenRecord::class, $token->hash()->value());

        if ($record === null) {
            $this->entityManager->persist(AccessTokenRecord::fromDomain($token));
        }

        $this->entityManager->flush();
    }

    public function findByHash(TokenHash $hash): ?AccessToken
    {
        $record = $this->entityManager->find(AccessTokenRecord::class, $hash->value());

        return $record?->toDomain();
    }

    public function delete(AccessToken $token): void
    {
        $record = $this->entityManager->find(AccessTokenRecord::class, $token->hash()->value());

        if ($record === null) {
            return;
        }

        $this->entityManager->remove($record);
        $this->entityManager->flush();
    }

    public function deleteByUserId(UserId $userId): void
    {
        $this->entityManager->createQuery(
            'DELETE FROM App\Auth\Infrastructure\Persistence\Doctrine\Entity\AccessTokenRecord token WHERE token.userId = :userId',
        )
            ->setParameter('userId', $userId->value())
            ->execute();
    }
}
