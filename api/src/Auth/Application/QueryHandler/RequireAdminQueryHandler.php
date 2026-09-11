<?php

declare(strict_types=1);

namespace App\Auth\Application\QueryHandler;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\Query\RequireAdminQuery;
use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Domain\Exception\Unauthenticated;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;

final readonly class RequireAdminQueryHandler
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private UserRepository $userRepository,
    ) {
    }

    public function handle(RequireAdminQuery $query): UserId
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery($query->token));
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new Unauthenticated();
        }

        if ($user->role()->value() !== Role::admin()->value()) {
            throw new Forbidden();
        }

        return $userId;
    }
}
