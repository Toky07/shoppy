<?php

declare(strict_types=1);

namespace App\User\Application\QueryHandler;

use App\User\Application\Query\GetUserQuery;
use App\User\Application\Response\UserResponse;
use App\User\Domain\Exception\UserNotFound;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\UserId;

final readonly class GetUserQueryHandler
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function handle(GetUserQuery $query): UserResponse
    {
        $id = UserId::fromString($query->id);
        $user = $this->userRepository->findById($id);

        if ($user === null) {
            throw new UserNotFound($id);
        }

        return UserResponse::fromUser($user);
    }
}
