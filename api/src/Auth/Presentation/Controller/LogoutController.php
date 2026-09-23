<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Controller;

use App\Auth\Application\Command\LogoutCommand;
use App\Auth\Application\CommandHandler\LogoutCommandHandler;
use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Http\SessionCookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class LogoutController
{
    public function __construct(
        private LogoutCommandHandler $logout,
        private CurrentUser $currentUser,
        private SessionCookie $sessionCookie,
    ) {
    }

    #[Route('/auth/logout', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $this->logout->handle(new LogoutCommand($this->currentUser->token()));

        $response = new Response(status: Response::HTTP_NO_CONTENT);
        $this->sessionCookie->clear($response, $request);

        return $response;
    }
}
