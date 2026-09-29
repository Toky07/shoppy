<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\User\Application\Query\ListUsersQuery;
use App\User\Application\QueryHandler\ListUsersQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ListUsersController
{
    public function __construct(
        private ListUsersQueryHandler $listUsers,
    ) {
    }

    #[Route('/admin/users', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]
    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->query->get('q');

        $response = $this->listUsers->handle(new ListUsersQuery(
            page: $request->query->getInt('page', ListUsersQuery::DEFAULT_PAGE),
            limit: $request->query->getInt('limit', ListUsersQuery::DEFAULT_LIMIT),
            search: is_string($search) ? $search : null,
        ));

        return new JsonResponse($response->toArray());
    }
}
