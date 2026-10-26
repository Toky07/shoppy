<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\LoginCommand;
use App\Auth\Application\IssueSession;
use App\Auth\Application\PasswordHasher;
use App\Auth\Application\Response\LoginResult;
use App\Auth\Domain\Exception\InvalidCredentials;
use App\Auth\Domain\Exception\InvalidPassword;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;

final readonly class LoginCommandHandler
{
    private const DUMMY_HASH = '$2y$10$DI1egvAO/Uv/./Y4yhQTTOHZ7E5GGZ.bnvkIFcJXa0iitPxKDD5IO';

    public function __construct(
        private UserRepository $userRepository,
        private CredentialsRepository $credentialsRepository,
        private PasswordHasher $passwordHasher,
        private IssueSession $sessions,
    ) {
    }

    public function handle(LoginCommand $command): LoginResult
    {
        try {
            $password = PlainPassword::forVerification($command->password);
        } catch (InvalidPassword) {
            throw new InvalidCredentials();
        }

        $user = $this->userRepository->findByEmail(Email::fromString($command->email));
        $credentials = $user === null ? null : $this->credentialsRepository->findByUserId($user->id());
        $hash = $credentials?->hashedPassword() ?? HashedPassword::fromHash(self::DUMMY_HASH);
        $passwordMatches = $this->passwordHasher->verify($hash, $password);

        if (
            $user === null
            || $user->isDeleted()
            || $credentials === null
            || !$passwordMatches
        ) {
            throw new InvalidCredentials();
        }

        $issued = $this->sessions->issue($user->id());

        return new LoginResult($issued->userId, $issued->accessToken, $issued->refreshToken);
    }
}
