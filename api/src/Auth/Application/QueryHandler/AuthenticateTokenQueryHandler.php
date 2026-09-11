<?php

declare(strict_types=1);

namespace App\Auth\Application\QueryHandler;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\TokenGenerator;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Shared\Domain\Clock;
use App\User\Domain\ValueObject\UserId;

final readonly class AuthenticateTokenQueryHandler
{
    public function __construct(
        private AccessTokenRepository $accessTokenRepository,
        private TokenGenerator $tokenGenerator,
        private Clock $clock,
    ) {
    }

    public function handle(AuthenticateTokenQuery $query): UserId
    {
        $token = $this->accessTokenRepository->findByHash($this->tokenGenerator->hash($query->token));

        if ($token === null || $token->isExpired($this->clock->now())) {
            throw new Unauthenticated();
        }

        return $token->userId();
    }
}
