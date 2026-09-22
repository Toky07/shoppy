<?php

declare(strict_types=1);

namespace App\Auth\Application;

use App\Auth\Domain\Entity\AccountToken;
use App\Auth\Domain\Exception\InvalidAccountToken;
use App\Auth\Domain\Repository\AccountTokenRepository;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\Shared\Domain\Clock;
use App\User\Domain\ValueObject\UserId;
use DateInterval;

final readonly class IssueAccountToken
{
    public function __construct(
        private AccountTokenRepository $tokens,
        private TokenGenerator $tokenGenerator,
        private Clock $clock,
    ) {
    }

    public function issue(UserId $userId, AccountTokenPurpose $purpose, string $ttl, ?string $subject = null): string
    {
        $this->tokens->deleteUnused($userId, $purpose);
        $generated = $this->tokenGenerator->generate();
        $now = $this->clock->now();

        $this->tokens->save(AccountToken::issue(
            $generated->hash,
            $userId,
            $purpose,
            $now->add(new DateInterval($ttl)),
            $now,
            $subject,
        ));

        return $generated->plain;
    }

    public function consume(string $plainToken, AccountTokenPurpose $purpose): AccountToken
    {
        $token = $this->tokens->findByHash($this->tokenGenerator->hash($plainToken));

        if ($token === null) {
            throw new InvalidAccountToken();
        }

        $token->consume($this->clock->now(), $purpose);
        $this->tokens->save($token);

        return $token;
    }
}
