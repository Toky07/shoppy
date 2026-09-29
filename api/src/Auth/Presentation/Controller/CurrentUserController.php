<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Presentation\Security\AuthenticationAttributes;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class CurrentUserController
{
    public function __construct(
        private CurrentUser $currentUser,
        private GetUserQueryHandler $getUser,
    ) {
    }

    #[Route('/auth/me', methods: ['GET'])]
    #[IsGranted(AuthenticationAttributes::IS_AUTHENTICATED)]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $user = $this->getUser->handle(new GetUserQuery($userId));

        return new JsonResponse($user->toArray());
    }
}
