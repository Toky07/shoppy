<?php

declare(strict_types=1);

namespace App\User\Application\CommandHandler;

use App\Auth\Application\PasswordHasher;
use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\Shared\Domain\Clock;
use App\User\Application\Command\SeedDemoUsersCommand;
use App\User\Application\DemoAccounts;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;

final readonly class SeedDemoUsersCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private CredentialsRepository $credentialsRepository,
        private PasswordHasher $passwordHasher,
        private Clock $clock,
    ) {
    }

    public function handle(SeedDemoUsersCommand $command): int
    {
        unset($command);

        $created = 0;
        $created += $this->seed(DemoAccounts::ADMIN_EMAIL, Role::admin()) ? 1 : 0;
        $created += $this->seed(DemoAccounts::VISITOR_EMAIL, Role::customer()) ? 1 : 0;

        return $created;
    }

    private function seed(string $emailAddress, Role $role): bool
    {
        $email = Email::fromString($emailAddress);

        if ($this->userRepository->findByEmail($email) !== null) {
            return false;
        }

        $user = User::register(UserId::generate(), $email, $this->clock->now(), $role);
        $user->markEmailVerified($this->clock->now());
        $this->userRepository->save($user);
        $this->credentialsRepository->save(Credentials::create(
            $user->id(),
            $this->passwordHasher->hash(PlainPassword::fromString(DemoAccounts::PASSWORD)),
        ));

        return true;
    }
}
