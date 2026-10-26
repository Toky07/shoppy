<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\RefreshSessionCommand;
use App\Auth\Application\IssueSession;
use App\Auth\Application\Response\IssuedSession;
use App\Auth\Application\TokenGenerator;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Shared\Domain\Clock;

final readonly class RefreshSessionCommandHandler
{
    public function __construct(
        private AccessTokenRepository $tokens,
        private TokenGenerator $tokenGenerator,
        private Clock $clock,
        private IssueSession $sessions,
    ) {
    }

    public function handle(RefreshSessionCommand $command): IssuedSession
    {
        if ($command->refreshToken === '') {
            throw new Unauthenticated();
        }

        $token = $this->tokens->findByHash($this->tokenGenerator->hash($command->refreshToken));

        if (
            $token === null
            || !$token->kind()->isRefresh()
            || $token->isExpired($this->clock->now())
        ) {
            throw new Unauthenticated();
        }

        $this->tokens->delete($token);

        return $this->sessions->issue($token->userId());
    }
}
