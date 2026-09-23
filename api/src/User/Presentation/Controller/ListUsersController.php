<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\User\Application\Query\ListUsersQuery;
use App\User\Application\QueryHandler\ListUsersQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListUsersController
{
    public function __construct(
        private CurrentUser $currentUser,
        private ListUsersQueryHandler $listUsers,
    ) {
    }

    #[Route('/admin/users', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $this->currentUser->requireAdmin();

        $search = $request->query->get('q');

        $response = $this->listUsers->handle(new ListUsersQuery(
            page: $request->query->getInt('page', ListUsersQuery::DEFAULT_PAGE),
            limit: $request->query->getInt('limit', ListUsersQuery::DEFAULT_LIMIT),
            search: is_string($search) ? $search : null,
        ));

        return new JsonResponse($response->toArray());
    }
}
