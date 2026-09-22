<?php

declare(strict_types=1);

namespace App\User\Application\Response;

use App\User\Domain\Entity\User;
use DateTimeInterface;

final readonly class UserResponse
{
    public function __construct(
        public string $id,
        public string $email,
        public string $createdAt,
        public string $role,
        public bool $emailVerified,
    ) {
    }

    public static function fromUser(User $user): self
    {
        return new self(
            $user->id()->value(),
            $user->email()->value(),
            $user->createdAt()->format(DateTimeInterface::ATOM),
            $user->role()->value(),
            $user->isEmailVerified(),
        );
    }

    /**
     * @return array{id: string, email: string, createdAt: string, role: string, emailVerified: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'createdAt' => $this->createdAt,
            'role' => $this->role,
            'emailVerified' => $this->emailVerified,
        ];
    }
}
