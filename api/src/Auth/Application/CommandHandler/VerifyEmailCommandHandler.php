<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\VerifyEmailCommand;
use App\Auth\Application\IssueAccountToken;
use App\Auth\Domain\Exception\InvalidAccountToken;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\Shared\Domain\Clock;
use App\User\Domain\Repository\UserRepository;

final readonly class VerifyEmailCommandHandler
{
    public function __construct(
        private IssueAccountToken $tokens,
        private UserRepository $users,
        private Clock $clock,
    ) {
    }

    public function handle(VerifyEmailCommand $command): void
    {
        $token = $this->tokens->consume($command->token, AccountTokenPurpose::emailVerification());
        $user = $this->users->findById($token->userId());

        if ($user === null || $user->isDeleted()) {
            throw new InvalidAccountToken();
        }

        $user->markEmailVerified($this->clock->now());
        $this->users->save($user);
    }
}
