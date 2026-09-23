<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetUserController
{
    public function __construct(
        private CurrentUser $currentUser,
        private GetUserQueryHandler $getUser,
    ) {
    }

    #[Route('/users/{id}', methods: ['GET'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $this->currentUser->assertSelfOrAdmin($id);

        $user = $this->getUser->handle(new GetUserQuery($id));

        return new JsonResponse($user->toArray());
    }
}
