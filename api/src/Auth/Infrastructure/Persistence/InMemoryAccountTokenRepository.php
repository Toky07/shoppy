<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence;

use App\Auth\Domain\Entity\AccountToken;
use App\Auth\Domain\Repository\AccountTokenRepository;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;

final class InMemoryAccountTokenRepository implements AccountTokenRepository
{
    /** @var array<string, AccountToken> */
    private array $tokens = [];

    public function save(AccountToken $token): void
    {
        $this->tokens[$token->hash()->value()] = $token;
    }

    public function findByHash(TokenHash $hash): ?AccountToken
    {
        return $this->tokens[$hash->value()] ?? null;
    }

    public function deleteUnused(UserId $userId, AccountTokenPurpose $purpose): void
    {
        foreach ($this->tokens as $hash => $token) {
            if (
                $token->userId()->value() === $userId->value()
                && $token->purpose()->equals($purpose)
                && $token->consumedAt() === null
            ) {
                unset($this->tokens[$hash]);
            }
        }
    }

    public function deleteByUserId(UserId $userId): void
    {
        foreach ($this->tokens as $hash => $token) {
            if ($token->userId()->value() === $userId->value()) {
                unset($this->tokens[$hash]);
            }
        }
    }
}
