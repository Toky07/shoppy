<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\ConfirmEmailChangeCommand;
use App\Auth\Application\IssueAccountToken;
use App\Auth\Domain\Exception\InvalidAccountToken;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\Shared\Domain\Clock;
use App\User\Domain\Exception\EmailAlreadyRegistered;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;

final readonly class ConfirmEmailChangeCommandHandler
{
    public function __construct(
        private IssueAccountToken $tokens,
        private UserRepository $users,
        private AccessTokenRepository $accessTokens,
        private Clock $clock,
    ) {
    }

    public function handle(ConfirmEmailChangeCommand $command): void
    {
        $token = $this->tokens->consume($command->token, AccountTokenPurpose::emailChange());
        $subject = $token->subject();

        if ($subject === null) {
            throw new InvalidAccountToken();
        }

        $email = Email::fromString($subject);
        $existing = $this->users->findByEmail($email);

        if ($existing !== null && $existing->id()->value() !== $token->userId()->value()) {
            throw new EmailAlreadyRegistered($email);
        }

        $user = $this->users->findById($token->userId());

        if ($user === null || $user->isDeleted()) {
            throw new InvalidAccountToken();
        }

        $user->changeEmail($email);
        $user->markEmailVerified($this->clock->now());
        $this->users->save($user);
        $this->accessTokens->deleteByUserId($user->id());
    }
}
