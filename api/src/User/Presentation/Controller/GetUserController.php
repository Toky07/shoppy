<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Auth\Presentation\Security\GrantChecker;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use App\User\Presentation\Security\UserAccessSubject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetUserController
{
    public function __construct(
        private GrantChecker $grantChecker,
        private GetUserQueryHandler $getUser,
    ) {
    }

    #[Route('/users/{id}', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $this->grantChecker->denyUnlessGranted(
            AuthorizationAttributes::USER_VIEW,
            new UserAccessSubject($id),
        );

        $user = $this->getUser->handle(new GetUserQuery($id));

        return new JsonResponse($user->toArray());
    }
}
