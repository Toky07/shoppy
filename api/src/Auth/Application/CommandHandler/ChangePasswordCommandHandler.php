<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\ChangePasswordCommand;
use App\Auth\Application\CurrentPassword;
use App\Auth\Application\PasswordHasher;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\User\Domain\ValueObject\UserId;

final readonly class ChangePasswordCommandHandler
{
    public function __construct(
        private CurrentPassword $passwords,
        private CredentialsRepository $credentials,
        private PasswordHasher $passwordHasher,
        private AccessTokenRepository $accessTokens,
    ) {
    }

    public function handle(ChangePasswordCommand $command): void
    {
        $userId = UserId::fromString($command->userId);
        $credentials = $this->passwords->require($userId, $command->currentPassword);
        $credentials->changePassword($this->passwordHasher->hash(PlainPassword::fromString($command->newPassword)));
        $this->credentials->save($credentials);
        $this->accessTokens->deleteByUserId($userId);
    }
}
