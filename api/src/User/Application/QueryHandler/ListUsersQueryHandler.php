<?php

declare(strict_types=1);

namespace App\User\Application\QueryHandler;

use App\User\Application\Query\ListUsersQuery;
use App\User\Application\Response\UserListResponse;
use App\User\Application\Response\UserResponse;
use App\User\Domain\Exception\InvalidUserPagination;
use App\User\Domain\Repository\UserRepository;

final readonly class ListUsersQueryHandler
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function handle(ListUsersQuery $query): UserListResponse
    {
        if ($query->page < 1 || $query->limit < 1 || $query->limit > ListUsersQuery::MAX_LIMIT) {
            throw new InvalidUserPagination();
        }

        $search = $this->normalizedSearch($query->search);
        $offset = ($query->page - 1) * $query->limit;

        return new UserListResponse(
            array_map(
                UserResponse::fromUser(...),
                $this->userRepository->findPage($offset, $query->limit, $search),
            ),
            $query->page,
            $query->limit,
            $this->userRepository->countAll($search),
        );
    }

    private function normalizedSearch(?string $search): ?string
    {
        if ($search === null) {
            return null;
        }

        $normalized = trim($search);

        return $normalized === '' ? null : $normalized;
    }
}
