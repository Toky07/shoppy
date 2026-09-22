<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\ResetPasswordCommand;
use App\Auth\Application\IssueAccountToken;
use App\Auth\Application\PasswordHasher;
use App\Auth\Domain\Exception\InvalidAccountToken;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\User\Domain\Repository\UserRepository;

final readonly class ResetPasswordCommandHandler
{
    public function __construct(
        private IssueAccountToken $tokens,
        private UserRepository $users,
        private CredentialsRepository $credentials,
        private PasswordHasher $passwordHasher,
        private AccessTokenRepository $accessTokens,
    ) {
    }

    public function handle(ResetPasswordCommand $command): void
    {
        $token = $this->tokens->consume($command->token, AccountTokenPurpose::passwordReset());
        $user = $this->users->findById($token->userId());
        $credentials = $user === null || $user->isDeleted() ? null : $this->credentials->findByUserId($user->id());

        if ($user === null || $credentials === null) {
            throw new InvalidAccountToken();
        }

        $credentials->changePassword($this->passwordHasher->hash(PlainPassword::fromString($command->password)));
        $this->credentials->save($credentials);
        $this->accessTokens->deleteByUserId($user->id());
    }
}
