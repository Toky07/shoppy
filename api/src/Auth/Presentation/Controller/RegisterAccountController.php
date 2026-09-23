<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Controller;

use App\Auth\Application\Command\RegisterAccountCommand;
use App\Auth\Application\CommandHandler\RegisterAccountCommandHandler;
use App\Auth\Presentation\Request\RegisterAccountHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RegisterAccountController
{
    public function __construct(
        private RegisterAccountCommandHandler $registerAccount,
    ) {
    }

    #[Route('/users', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $httpRequest = RegisterAccountHttpRequest::fromPayload($request->toArray());

        $this->registerAccount->handle(new RegisterAccountCommand(
            email: $httpRequest->email,
            password: $httpRequest->password,
        ));

        return new JsonResponse(['status' => 'accepted'], Response::HTTP_CREATED);
    }
}
