<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Presentation\Http\CurrentUser;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use App\User\Presentation\Security\UserAccessSubject;
use App\User\Presentation\Security\UserVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Presentation\Security\AuthenticationAttributes;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class GetUserController
{
    public function __construct(
        private CurrentUser $currentUser,
        private AuthorizationCheckerInterface $authorizationChecker,
        private GetUserQueryHandler $getUser,
    ) {
    }

    #[Route('/users/{id}', methods: ['GET'])]
    #[IsGranted(AuthenticationAttributes::IS_AUTHENTICATED)]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $this->currentUser->id();

        if (!$this->authorizationChecker->isGranted(
            UserVoter::VIEW,
            new UserAccessSubject($id),
        )) {
            throw new Forbidden();
        }

        $user = $this->getUser->handle(new GetUserQuery($id));

        return new JsonResponse($user->toArray());
    }
}
