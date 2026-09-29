<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use App\User\Presentation\Security\UserAccessSubject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetUserController
{
    public function __construct(
        private GetUserQueryHandler $getUser,
    ) {
    }

    #[Route('/users/{id}', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    #[IsGranted(AuthorizationAttributes::USER_VIEW, subject: 'userAccess')]
    public function __invoke(UserAccessSubject $userAccess): JsonResponse
    {
        $user = $this->getUser->handle(new GetUserQuery($userAccess->userId));

        return new JsonResponse($user->toArray());
    }
}
