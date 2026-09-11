<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Auth\Application\Query\RequireSelfOrAdminQuery;
use App\Auth\Application\QueryHandler\RequireSelfOrAdminQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetUserController
{
    public function __construct(
        private RequireSelfOrAdminQueryHandler $requireSelfOrAdmin,
        private GetUserQueryHandler $getUser,
    ) {
    }

    #[Route('/users/{id}', methods: ['GET'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $this->requireSelfOrAdmin->handle(new RequireSelfOrAdminQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
            $id,
        ));

        $user = $this->getUser->handle(new GetUserQuery($id));

        return new JsonResponse($user->toArray());
    }
}
