<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Controller;

use App\Auth\Application\Command\LoginCommand;
use App\Auth\Application\CommandHandler\LoginCommandHandler;
use App\Auth\Presentation\Request\LoginHttpRequest;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class LoginController
{
    public function __construct(
        private LoginCommandHandler $login,
        private GetUserQueryHandler $getUser,
    ) {
    }

    #[Route('/auth/login', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $httpRequest = LoginHttpRequest::fromPayload($request->toArray());

        $result = $this->login->handle(new LoginCommand(
            email: $httpRequest->email,
            password: $httpRequest->password,
        ));

        $user = $this->getUser->handle(new GetUserQuery($result->userId->value()));

        return new JsonResponse([
            'accessToken' => $result->accessToken,
            'user' => $user->toArray(),
        ]);
    }
}
