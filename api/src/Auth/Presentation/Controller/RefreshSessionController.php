<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Controller;

use App\Auth\Application\Command\RefreshSessionCommand;
use App\Auth\Application\CommandHandler\RefreshSessionCommandHandler;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Presentation\Http\RefreshCredential;
use App\Auth\Presentation\Http\SessionCookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RefreshSessionController
{
    public function __construct(
        private RefreshSessionCommandHandler $refreshSession,
        private SessionCookie $sessionCookie,
    ) {
    }

    #[Route('/auth/refresh', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $refreshToken = RefreshCredential::fromRequest($request);

        if ($refreshToken === null) {
            throw new Unauthenticated();
        }

        $issued = $this->refreshSession->handle(new RefreshSessionCommand($refreshToken));
        $response = new Response(status: Response::HTTP_NO_CONTENT);
        $this->sessionCookie->attach($response, $request, $issued->accessToken, $issued->refreshToken);

        return $response;
    }
}
