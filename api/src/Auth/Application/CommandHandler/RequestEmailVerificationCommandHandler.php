<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\AccountNotifier;
use App\Auth\Application\Command\RequestEmailVerificationCommand;
use App\Auth\Application\IssueAccountToken;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\User\Domain\Exception\UserNotFound;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\UserId;

final readonly class RequestEmailVerificationCommandHandler
{
    public function __construct(
        private UserRepository $users,
        private IssueAccountToken $tokens,
        private AccountNotifier $notifier,
    ) {
    }

    public function handle(RequestEmailVerificationCommand $command): void
    {
        $userId = UserId::fromString($command->userId);
        $user = $this->users->findById($userId);

        if ($user === null || $user->isDeleted()) {
            throw new UserNotFound($userId);
        }

        if ($user->isEmailVerified()) {
            return;
        }

        $plain = $this->tokens->issue($user->id(), AccountTokenPurpose::emailVerification(), 'P1D');
        $this->notifier->sendEmailVerification($user->email()->value(), $plain);
    }
}
