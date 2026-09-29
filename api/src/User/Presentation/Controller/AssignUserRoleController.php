<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\User\Application\Command\AssignUserRoleCommand;
use App\User\Application\CommandHandler\AssignUserRoleCommandHandler;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use App\User\Presentation\Request\AssignUserRoleHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class AssignUserRoleController
{
    public function __construct(
        private AssignUserRoleCommandHandler $assignUserRole,
        private GetUserQueryHandler $getUser,
    ) {
    }

    #[Route('/users/{id}', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $httpRequest = AssignUserRoleHttpRequest::fromPayload($request->toArray());

        $this->assignUserRole->handle(new AssignUserRoleCommand(
            id: $id,
            role: $httpRequest->role,
        ));

        $user = $this->getUser->handle(new GetUserQuery($id));

        return new JsonResponse($user->toArray());
    }
}
