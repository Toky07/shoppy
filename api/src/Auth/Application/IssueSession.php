<?php

declare(strict_types=1);

namespace App\Auth\Application;

use App\Auth\Application\Response\IssuedSession;
use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\ValueObject\AccessTokenKind;
use App\Shared\Domain\Clock;
use App\User\Domain\ValueObject\UserId;
use DateInterval;

final readonly class IssueSession
{
    public const ACCESS_TTL = 'PT1H';

    public const REFRESH_TTL = 'P7D';

    public const MAX_SESSIONS = 5;

    public function __construct(
        private AccessTokenRepository $tokens,
        private TokenGenerator $tokenGenerator,
        private Clock $clock,
    ) {
    }

    public function issue(UserId $userId): IssuedSession
    {
        $now = $this->clock->now();
        $access = $this->tokenGenerator->generate();
        $refresh = $this->tokenGenerator->generate();

        $this->tokens->save(AccessToken::issue(
            $userId,
            $access->hash,
            $now->add(new DateInterval(self::ACCESS_TTL)),
            $now,
            AccessTokenKind::access(),
        ));
        $this->tokens->save(AccessToken::issue(
            $userId,
            $refresh->hash,
            $now->add(new DateInterval(self::REFRESH_TTL)),
            $now,
            AccessTokenKind::refresh(),
        ));
        $this->tokens->trimTo($userId, self::MAX_SESSIONS, AccessTokenKind::access());
        $this->tokens->trimTo($userId, self::MAX_SESSIONS, AccessTokenKind::refresh());

        return new IssuedSession($userId, $access->plain, $refresh->plain);
    }
}
