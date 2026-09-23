<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\LoginCommand;
use App\Auth\Application\PasswordHasher;
use App\Auth\Application\Response\LoginResult;
use App\Auth\Application\TokenGenerator;
use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\Exception\InvalidCredentials;
use App\Auth\Domain\Exception\InvalidPassword;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\Shared\Domain\Clock;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use DateInterval;

final readonly class LoginCommandHandler
{
    private const TOKEN_TTL = 'P7D';

    private const MAX_SESSIONS = 5;

    private const DUMMY_HASH = '$2y$10$DI1egvAO/Uv/./Y4yhQTTOHZ7E5GGZ.bnvkIFcJXa0iitPxKDD5IO';

    public function __construct(
        private UserRepository $userRepository,
        private CredentialsRepository $credentialsRepository,
        private PasswordHasher $passwordHasher,
        private AccessTokenRepository $accessTokenRepository,
        private TokenGenerator $tokenGenerator,
        private Clock $clock,
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

        $now = $this->clock->now();
        $generated = $this->tokenGenerator->generate();

        $this->accessTokenRepository->save(AccessToken::issue(
            $user->id(),
            $generated->hash,
            $now->add(new DateInterval(self::TOKEN_TTL)),
            $now,
        ));
        $this->accessTokenRepository->trimTo($user->id(), self::MAX_SESSIONS);

        return new LoginResult($user->id(), $generated->plain);
    }
}
