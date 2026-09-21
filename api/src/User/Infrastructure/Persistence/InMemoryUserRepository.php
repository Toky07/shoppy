<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Persistence;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<string, User> */
    private array $users = [];

    public function save(User $user): void
    {
        $this->users[$user->id()->value()] = $user;
    }

    public function findById(UserId $id): ?User
    {
        return $this->users[$id->value()] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->email()->value() === $email->value()) {
                return $user;
            }
        }

        return null;
    }

    public function findPage(int $offset, int $limit, ?string $search = null): array
    {
        $users = $this->filtered($search);

        usort(
            $users,
            static fn (User $left, User $right): int => [$right->createdAt(), $right->id()->value()]
                <=> [$left->createdAt(), $left->id()->value()],
        );

        return array_values(array_slice($users, $offset, $limit));
    }

    public function countAll(?string $search = null): int
    {
        return count($this->filtered($search));
    }

    /**
     * @return list<User>
     */
    private function filtered(?string $search): array
    {
        $users = array_values($this->users);
        if ($search === null) {
            return $users;
        }

        $needle = mb_strtolower($search);

        return array_values(array_filter(
            $users,
            static fn (User $user): bool => str_contains($user->email()->value(), $needle),
        ));
    }
}
