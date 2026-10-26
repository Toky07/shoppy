<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;

final class CountingUserRepository implements UserRepository
{
    public int $findByIdCalls = 0;

    public int $findByIdsCalls = 0;

    public function __construct(private UserRepository $inner)
    {
    }

    public function save(User $user): void
    {
        $this->inner->save($user);
    }

    public function findById(UserId $id): ?User
    {
        $this->findByIdCalls++;

        return $this->inner->findById($id);
    }

    public function findByIds(array $ids): array
    {
        $this->findByIdsCalls++;

        return $this->inner->findByIds($ids);
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->inner->findByEmail($email);
    }

    public function findPage(int $offset, int $limit, ?string $search = null): array
    {
        return $this->inner->findPage($offset, $limit, $search);
    }

    public function countAll(?string $search = null): int
    {
        return $this->inner->countAll($search);
    }

    public function countWithRole(Role $role): int
    {
        return $this->inner->countWithRole($role);
    }
}
