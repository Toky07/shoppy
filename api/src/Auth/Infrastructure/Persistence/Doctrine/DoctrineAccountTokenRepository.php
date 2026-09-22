<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Doctrine;

use App\Auth\Domain\Entity\AccountToken;
use App\Auth\Domain\Repository\AccountTokenRepository;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\Auth\Domain\ValueObject\TokenHash;
use App\Auth\Infrastructure\Persistence\Doctrine\Entity\AccountTokenRecord;
use App\User\Domain\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAccountTokenRepository implements AccountTokenRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(AccountToken $token): void
    {
        $record = $this->entityManager->find(AccountTokenRecord::class, $token->hash()->value());

        if ($record === null) {
            $this->entityManager->persist(AccountTokenRecord::fromDomain($token));
        } else {
            $record->updateFromDomain($token);
        }

        $this->entityManager->flush();
    }

    public function findByHash(TokenHash $hash): ?AccountToken
    {
        $record = $this->entityManager->find(AccountTokenRecord::class, $hash->value());

        return $record?->toDomain();
    }

    public function deleteUnused(UserId $userId, AccountTokenPurpose $purpose): void
    {
        $this->entityManager->createQuery(
            'DELETE FROM App\Auth\Infrastructure\Persistence\Doctrine\Entity\AccountTokenRecord token
             WHERE token.userId = :userId AND token.purpose = :purpose AND token.consumedAt IS NULL',
        )
            ->setParameter('userId', $userId->value())
            ->setParameter('purpose', $purpose->value())
            ->execute();
    }

    public function deleteByUserId(UserId $userId): void
    {
        $this->entityManager->createQuery(
            'DELETE FROM App\Auth\Infrastructure\Persistence\Doctrine\Entity\AccountTokenRecord token WHERE token.userId = :userId',
        )
            ->setParameter('userId', $userId->value())
            ->execute();
    }
}
