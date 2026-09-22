<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entity;

use App\Auth\Domain\Exception\InvalidAccountToken;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class AccountToken
{
    private function __construct(
        private TokenHash $hash,
        private UserId $userId,
        private AccountTokenPurpose $purpose,
        private DateTimeImmutable $expiresAt,
        private DateTimeImmutable $createdAt,
        private ?string $subject,
        private ?DateTimeImmutable $consumedAt,
    ) {
    }

    public static function issue(
        TokenHash $hash,
        UserId $userId,
        AccountTokenPurpose $purpose,
        DateTimeImmutable $expiresAt,
        DateTimeImmutable $createdAt,
        ?string $subject = null,
    ): self {
        return new self($hash, $userId, $purpose, $expiresAt, $createdAt, $subject, null);
    }

    public static function reconstitute(
        TokenHash $hash,
        UserId $userId,
        AccountTokenPurpose $purpose,
        DateTimeImmutable $expiresAt,
        DateTimeImmutable $createdAt,
        ?string $subject,
        ?DateTimeImmutable $consumedAt,
    ): self {
        return new self($hash, $userId, $purpose, $expiresAt, $createdAt, $subject, $consumedAt);
    }

    public function hash(): TokenHash
    {
        return $this->hash;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function purpose(): AccountTokenPurpose
    {
        return $this->purpose;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function subject(): ?string
    {
        return $this->subject;
    }

    public function consumedAt(): ?DateTimeImmutable
    {
        return $this->consumedAt;
    }

    public function consume(DateTimeImmutable $now, AccountTokenPurpose $purpose): void
    {
        if (!$this->purpose->equals($purpose) || $this->consumedAt !== null || $now >= $this->expiresAt) {
            throw new InvalidAccountToken();
        }

        $this->consumedAt = $now;
    }
}
