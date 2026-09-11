<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Persistence\Doctrine;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\Doctrine\Entity\UserRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserRepository implements UserRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(User $user): void
    {
        $record = $this->entityManager->find(UserRecord::class, $user->id()->value());

        if ($record === null) {
            $this->entityManager->persist(UserRecord::fromDomain($user));
        } else {
            $record->updateFromDomain($user);
        }

        $this->entityManager->flush();
    }

    public function findById(UserId $id): ?User
    {
        $record = $this->entityManager->find(UserRecord::class, $id->value());

        return $record?->toDomain();
    }

    public function findByEmail(Email $email): ?User
    {
        $record = $this->entityManager->getRepository(UserRecord::class)->findOneBy([
            'email' => $email->value(),
        ]);

        return $record?->toDomain();
    }
}
