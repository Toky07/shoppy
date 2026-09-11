<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class User
{
    private function __construct(
        private UserId $id,
        private Email $email,
        private DateTimeImmutable $createdAt,
        private Role $role,
    ) {
    }

    public static function register(
        UserId $id,
        Email $email,
        DateTimeImmutable $createdAt,
        ?Role $role = null,
    ): self {
        return new self($id, $email, $createdAt, $role ?? Role::customer());
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function assignRole(Role $role): void
    {
        $this->role = $role;
    }
}
