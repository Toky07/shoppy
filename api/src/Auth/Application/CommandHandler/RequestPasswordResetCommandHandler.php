<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\AccountNotifier;
use App\Auth\Application\Command\RequestPasswordResetCommand;
use App\Auth\Application\IssueAccountToken;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;

final readonly class RequestPasswordResetCommandHandler
{
    public function __construct(
        private UserRepository $users,
        private IssueAccountToken $tokens,
        private AccountNotifier $notifier,
    ) {
    }

    public function handle(RequestPasswordResetCommand $command): void
    {
        $email = Email::fromString($command->email);
        $user = $this->users->findByEmail($email);

        if ($user === null || $user->isDeleted()) {
            return;
        }

        $plain = $this->tokens->issue($user->id(), AccountTokenPurpose::passwordReset(), 'PT1H');
        $this->notifier->sendPasswordReset($user->email()->value(), $plain);
    }
}
