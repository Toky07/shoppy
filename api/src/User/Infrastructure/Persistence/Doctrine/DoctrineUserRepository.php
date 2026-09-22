<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Persistence\Doctrine;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\Doctrine\Entity\UserRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

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

    public function findPage(int $offset, int $limit, ?string $search = null): array
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(UserRecord::class, 'u')
            ->andWhere('u.deletedAt IS NULL')
            ->orderBy('u.createdAt', 'DESC')
            ->addOrderBy('u.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $this->applySearch($query, $search);

        return array_map(
            static fn (UserRecord $record): User => $record->toDomain(),
            $query->getQuery()->getResult(),
        );
    }

    public function countAll(?string $search = null): int
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(UserRecord::class, 'u')
            ->andWhere('u.deletedAt IS NULL');

        $this->applySearch($query, $search);

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    public function countWithRole(Role $role): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(UserRecord::class, 'u')
            ->andWhere('u.role = :role')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('role', $role->value())
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function applySearch(QueryBuilder $query, ?string $search): void
    {
        if ($search === null) {
            return;
        }

        $query
            ->andWhere('LOWER(u.email) LIKE :search')
            ->setParameter('search', '%'.mb_strtolower($search).'%');
    }
}
