<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Controller;

use App\Auth\Application\Command\LogoutCommand;
use App\Auth\Application\CommandHandler\LogoutCommandHandler;
use App\Auth\Presentation\Http\BearerToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class LogoutController
{
    public function __construct(
        private LogoutCommandHandler $logout,
    ) {
    }

    #[Route('/auth/logout', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $this->logout->handle(new LogoutCommand(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
