<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Controller;

use App\Auth\Application\Command\RegisterAccountCommand;
use App\Auth\Application\CommandHandler\RegisterAccountCommandHandler;
use App\Auth\Presentation\Request\RegisterAccountHttpRequest;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RegisterAccountController
{
    public function __construct(
        private RegisterAccountCommandHandler $registerAccount,
        private GetUserQueryHandler $getUser,
    ) {
    }

    #[Route('/users', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $httpRequest = RegisterAccountHttpRequest::fromPayload($request->toArray());

        $id = $this->registerAccount->handle(new RegisterAccountCommand(
            email: $httpRequest->email,
            password: $httpRequest->password,
        ));

        $user = $this->getUser->handle(new GetUserQuery($id->value()));

        return new JsonResponse(
            $user->toArray(),
            Response::HTTP_CREATED,
            ['Location' => '/users/'.$id->value()],
        );
    }
}
