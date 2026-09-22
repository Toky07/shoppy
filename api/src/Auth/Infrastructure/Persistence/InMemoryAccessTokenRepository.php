<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence;

use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;

final class InMemoryAccessTokenRepository implements AccessTokenRepository
{
    /** @var array<string, AccessToken> */
    private array $tokens = [];

    public function save(AccessToken $token): void
    {
        $this->tokens[$token->hash()->value()] = $token;
    }

    public function findByHash(TokenHash $hash): ?AccessToken
    {
        return $this->tokens[$hash->value()] ?? null;
    }

    public function delete(AccessToken $token): void
    {
        unset($this->tokens[$token->hash()->value()]);
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
