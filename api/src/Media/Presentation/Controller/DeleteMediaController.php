<?php

declare(strict_types=1);

namespace App\Media\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Media\Application\Command\DeleteMediaCommand;
use App\Media\Application\CommandHandler\DeleteMediaCommandHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class DeleteMediaController
{
    public function __construct(
        private CurrentUser $currentUser,
        private DeleteMediaCommandHandler $deleteMedia,
    ) {
    }

    #[Route('/media/{id}', methods: ['DELETE'])]
    public function __invoke(string $id, Request $request): Response
    {
        $this->currentUser->requireAdmin();

        $this->deleteMedia->handle(new DeleteMediaCommand($id));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
