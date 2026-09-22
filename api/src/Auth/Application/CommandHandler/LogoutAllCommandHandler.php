<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\LogoutAllCommand;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\User\Domain\ValueObject\UserId;

final readonly class LogoutAllCommandHandler
{
    public function __construct(private AccessTokenRepository $accessTokens)
    {
    }

    public function handle(LogoutAllCommand $command): void
    {
        $this->accessTokens->deleteByUserId(UserId::fromString($command->userId));
    }
}
