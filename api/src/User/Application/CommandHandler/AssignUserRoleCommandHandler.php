<?php

declare(strict_types=1);

namespace App\User\Application\CommandHandler;

use App\Shared\Application\Event\UserRoleChanged;
use App\User\Application\Command\AssignUserRoleCommand;
use App\User\Domain\Exception\UserNotFound;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class AssignUserRoleCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private ?EventDispatcherInterface $events = null,
    ) {
    }

    public function handle(AssignUserRoleCommand $command): void
    {
        $id = UserId::fromString($command->id);
        $user = $this->userRepository->findById($id);

        if ($user === null) {
            throw new UserNotFound($id);
        }

        $user->assignRole(Role::fromString($command->role));
        $this->userRepository->save($user);
        $this->events?->dispatch(new UserRoleChanged($id->value()));
    }
}
