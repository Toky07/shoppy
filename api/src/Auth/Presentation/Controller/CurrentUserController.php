<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class CurrentUserController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private GetUserQueryHandler $getUser,
    ) {
    }

    #[Route('/auth/me', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $user = $this->getUser->handle(new GetUserQuery($userId->value()));

        return new JsonResponse($user->toArray());
    }
}
