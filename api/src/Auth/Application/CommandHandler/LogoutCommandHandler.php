<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\LogoutCommand;
use App\Auth\Application\TokenGenerator;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Shared\Domain\Clock;

final readonly class LogoutCommandHandler
{
    public function __construct(
        private AccessTokenRepository $accessTokenRepository,
        private TokenGenerator $tokenGenerator,
        private Clock $clock,
    ) {
    }

    public function handle(LogoutCommand $command): void
    {
        $token = $this->accessTokenRepository->findByHash(
            $this->tokenGenerator->hash($command->token),
        );

        if ($token === null || $token->isExpired($this->clock->now())) {
            throw new Unauthenticated();
        }

        $this->accessTokenRepository->delete($token);
    }
}
