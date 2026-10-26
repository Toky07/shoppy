<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\PurgeExpiredAccessTokensCommand;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Shared\Domain\Clock;

final readonly class PurgeExpiredAccessTokensCommandHandler
{
    public function __construct(
        private AccessTokenRepository $tokens,
        private Clock $clock,
    ) {
    }

    public function handle(PurgeExpiredAccessTokensCommand $command): int
    {
        unset($command);

        return $this->tokens->deleteExpired($this->clock->now());
    }
}
