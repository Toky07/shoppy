<?php

declare(strict_types=1);

namespace App\Auth\Application;

use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\Exception\InvalidCredentials;
use App\Auth\Domain\Exception\InvalidPassword;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\User\Domain\ValueObject\UserId;

final readonly class CurrentPassword
{
    public function __construct(
        private CredentialsRepository $credentials,
        private PasswordHasher $passwordHasher,
    ) {
    }

    public function require(UserId $userId, string $password): Credentials
    {
        try {
            $plain = PlainPassword::fromString($password);
        } catch (InvalidPassword) {
            throw new InvalidCredentials();
        }

        $credentials = $this->credentials->findByUserId($userId);

        if ($credentials === null || !$this->passwordHasher->verify($credentials->hashedPassword(), $plain)) {
            throw new InvalidCredentials();
        }

        return $credentials;
    }
}
