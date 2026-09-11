<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entity;

use App\Auth\Domain\ValueObject\HashedPassword;
use App\User\Domain\ValueObject\UserId;

final class Credentials
{
    private function __construct(
        private UserId $userId,
        private HashedPassword $hashedPassword,
    ) {
    }

    public static function create(UserId $userId, HashedPassword $hashedPassword): self
    {
        return new self($userId, $hashedPassword);
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function hashedPassword(): HashedPassword
    {
        return $this->hashedPassword;
    }
}
