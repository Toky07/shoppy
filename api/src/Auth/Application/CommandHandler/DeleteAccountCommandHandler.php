<?php

declare(strict_types=1);

namespace App\Auth\Application\CommandHandler;

use App\Auth\Application\Command\DeleteAccountCommand;
use App\Auth\Application\CurrentPassword;
use App\Auth\Domain\Exception\LastAdminAccount;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\Repository\AccountTokenRepository;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Shared\Domain\Clock;
use App\User\Domain\Exception\UserNotFound;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;

final readonly class DeleteAccountCommandHandler
{
    public function __construct(
        private UserRepository $users,
        private CurrentPassword $passwords,
        private CredentialsRepository $credentials,
        private AccessTokenRepository $accessTokens,
        private AccountTokenRepository $accountTokens,
        private Clock $clock,
    ) {
    }

    public function handle(DeleteAccountCommand $command): void
    {
        $userId = UserId::fromString($command->userId);
        $user = $this->users->findById($userId);

        if ($user === null || $user->isDeleted()) {
            throw new UserNotFound($userId);
        }

        $this->passwords->require($userId, $command->password);

        if ($user->role()->value() === Role::admin()->value() && $this->users->countWithRole(Role::admin()) <= 1) {
            throw new LastAdminAccount();
        }

        $user->anonymize($this->clock->now());
        $this->users->save($user);
        $this->credentials->delete($userId);
        $this->accessTokens->deleteByUserId($userId);
        $this->accountTokens->deleteByUserId($userId);
    }
}
