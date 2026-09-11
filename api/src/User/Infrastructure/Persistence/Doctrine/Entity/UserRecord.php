<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Persistence\Doctrine\Entity;

use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_users_email', columns: ['email'])]
class UserRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(length: 32)]
    private string $role;

    public static function fromDomain(User $user): self
    {
        $record = new self();
        $record->apply($user);

        return $record;
    }

    public function updateFromDomain(User $user): void
    {
        $this->role = $user->role()->value();
    }

    public function toDomain(): User
    {
        return User::register(
            UserId::fromString($this->id),
            Email::fromString($this->email),
            $this->createdAt,
            Role::fromString($this->role),
        );
    }

    private function apply(User $user): void
    {
        $this->id = $user->id()->value();
        $this->email = $user->email()->value();
        $this->createdAt = $user->createdAt();
        $this->role = $user->role()->value();
    }
}
