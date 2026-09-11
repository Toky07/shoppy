<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entity;

use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class AccessToken
{
    private function __construct(
        private UserId $userId,
        private TokenHash $hash,
        private DateTimeImmutable $expiresAt,
        private DateTimeImmutable $createdAt,
    ) {
    }

    public static function issue(
        UserId $userId,
        TokenHash $hash,
        DateTimeImmutable $expiresAt,
        DateTimeImmutable $createdAt,
    ): self {
        return new self($userId, $hash, $expiresAt, $createdAt);
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function hash(): TokenHash
    {
        return $this->hash;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $now >= $this->expiresAt;
    }
}
