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
        private ?DateTimeImmutable $emailVerifiedAt = null,
        private ?DateTimeImmutable $deletedAt = null,
    ) {
    }

    public static function register(
        UserId $id,
        Email $email,
        DateTimeImmutable $createdAt,
        ?Role $role = null,
        ?DateTimeImmutable $emailVerifiedAt = null,
        ?DateTimeImmutable $deletedAt = null,
    ): self {
        return new self($id, $email, $createdAt, $role ?? Role::customer(), $emailVerifiedAt, $deletedAt);
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

    public function emailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null && $this->deletedAt === null;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function markEmailVerified(DateTimeImmutable $at): void
    {
        $this->emailVerifiedAt = $at;
    }

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
        $this->emailVerifiedAt = null;
    }

    public function anonymize(DateTimeImmutable $at): void
    {
        $this->email = Email::fromString('deleted.'.str_replace('-', '', $this->id->value()).'@users.invalid');
        $this->role = Role::customer();
        $this->emailVerifiedAt = null;
        $this->deletedAt = $at;
    }
}
