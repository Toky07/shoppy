<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Doctrine;

use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Auth\Infrastructure\Persistence\Doctrine\Entity\CredentialRecord;
use App\User\Domain\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCredentialsRepository implements CredentialsRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Credentials $credentials): void
    {
        $record = $this->entityManager->find(CredentialRecord::class, $credentials->userId()->value());

        if ($record === null) {
            $this->entityManager->persist(CredentialRecord::fromDomain($credentials));
        }

        $this->entityManager->flush();
    }

    public function findByUserId(UserId $userId): ?Credentials
    {
        $record = $this->entityManager->find(CredentialRecord::class, $userId->value());

        return $record?->toDomain();
    }
}
