<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\AccountNotifier;
use App\Auth\Application\Command\RequestEmailChangeCommand;
use App\Auth\Application\CurrentPassword;
use App\Auth\Application\IssueAccountToken;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\User\Domain\Exception\EmailAlreadyRegistered;
use App\User\Domain\Exception\EmailUnchanged;
use App\User\Domain\Exception\UserNotFound;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;

final readonly class RequestEmailChangeCommandHandler
{
    public function __construct(
        private UserRepository $users,
        private CurrentPassword $passwords,
        private IssueAccountToken $tokens,
        private AccountNotifier $notifier,
    ) {
    }

    public function handle(RequestEmailChangeCommand $command): void
    {
        $userId = UserId::fromString($command->userId);
        $user = $this->users->findById($userId);

        if ($user === null || $user->isDeleted()) {
            throw new UserNotFound($userId);
        }

        $this->passwords->require($userId, $command->currentPassword);
        $email = Email::fromString($command->email);

        if ($email->value() === $user->email()->value()) {
            throw new EmailUnchanged();
        }

        $existing = $this->users->findByEmail($email);

        if ($existing !== null) {
            throw new EmailAlreadyRegistered($email);
        }

        $plain = $this->tokens->issue($userId, AccountTokenPurpose::emailChange(), 'P1D', $email->value());
        $this->notifier->sendEmailChange($email->value(), $plain);
    }
}
