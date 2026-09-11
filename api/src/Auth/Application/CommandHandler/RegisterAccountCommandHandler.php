<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\RegisterAccountCommand;
use App\Auth\Application\PasswordHasher;
use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\User\Application\Command\RegisterUserCommand;
use App\User\Application\CommandHandler\RegisterUserCommandHandler;
use App\User\Domain\ValueObject\UserId;

final readonly class RegisterAccountCommandHandler
{
    public function __construct(
        private RegisterUserCommandHandler $registerUser,
        private CredentialsRepository $credentialsRepository,
        private PasswordHasher $passwordHasher,
    ) {
    }

    public function handle(RegisterAccountCommand $command): UserId
    {
        $hashedPassword = $this->passwordHasher->hash(PlainPassword::fromString($command->password));
        $userId = $this->registerUser->handle(new RegisterUserCommand($command->email));

        $this->credentialsRepository->save(Credentials::create($userId, $hashedPassword));

        return $userId;
    }
}
