<?php

declare(strict_types=1);

namespace App\User\Application\CommandHandler;

use App\Audit\Application\Port\AuditLogger;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditSubjectType;
use App\Shared\Application\Event\UserRoleChanged;
use App\User\Application\Command\AssignUserRoleCommand;
use App\User\Domain\Exception\LastAdminAccount;
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
        private ?AuditLogger $audit = null,
    ) {
    }

    public function handle(AssignUserRoleCommand $command): void
    {
        $id = UserId::fromString($command->id);
        $user = $this->userRepository->findById($id);

        if ($user === null) {
            throw new UserNotFound($id);
        }

        $role = Role::fromString($command->role);
        $previous = $user->role()->value();

        if (
            $previous === Role::admin()->value()
            && $role->value() !== Role::admin()->value()
            && $this->userRepository->countWithRole(Role::admin()) <= 1
        ) {
            throw new LastAdminAccount();
        }

        $user->assignRole($role);
        $this->userRepository->save($user);
        $this->events?->dispatch(new UserRoleChanged($id->value()));

        if ($previous !== $role->value()) {
            $this->audit?->record(AuditAction::ROLE_CHANGED, AuditSubjectType::USER, $id->value(), [
                'from' => $previous,
                'to' => $role->value(),
                'label' => $user->email()->value(),
            ]);
        }
    }
}
