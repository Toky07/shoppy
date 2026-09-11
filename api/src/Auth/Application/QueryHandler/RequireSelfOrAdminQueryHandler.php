<?php

declare(strict_types=1);

namespace App\Auth\Application\QueryHandler;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\Query\RequireSelfOrAdminQuery;
use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Domain\Exception\Unauthenticated;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;

final readonly class RequireSelfOrAdminQueryHandler
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private UserRepository $userRepository,
    ) {
    }

    public function handle(RequireSelfOrAdminQuery $query): UserId
    {
        $authenticatedId = $this->authenticateToken->handle(new AuthenticateTokenQuery($query->token));

        if ($authenticatedId->value() === $query->userId) {
            return $authenticatedId;
        }

        $user = $this->userRepository->findById($authenticatedId);

        if ($user === null) {
            throw new Unauthenticated();
        }

        if ($user->role()->value() !== Role::admin()->value()) {
            throw new Forbidden();
        }

        return $authenticatedId;
    }
}
