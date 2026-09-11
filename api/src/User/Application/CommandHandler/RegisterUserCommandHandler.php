<?php

declare(strict_types=1);

namespace App\User\Application\CommandHandler;

use App\Shared\Domain\Clock;
use App\User\Application\Command\RegisterUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\EmailAlreadyRegistered;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;

final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private Clock $clock,
    ) {
    }

    public function handle(RegisterUserCommand $command): UserId
    {
        $email = Email::fromString($command->email);

        if ($this->userRepository->findByEmail($email) !== null) {
            throw new EmailAlreadyRegistered($email);
        }

        $user = User::register(
            UserId::generate(),
            $email,
            $this->clock->now(),
        );

        $this->userRepository->save($user);

        return $user->id();
    }
}
