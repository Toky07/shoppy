<?php

declare(strict_types=1);

namespace App\Media\Presentation\Controller;

use App\Auth\Application\Query\RequireAdminQuery;
use App\Auth\Application\QueryHandler\RequireAdminQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Media\Application\Command\DeleteMediaCommand;
use App\Media\Application\CommandHandler\DeleteMediaCommandHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class DeleteMediaController
{
    public function __construct(
        private RequireAdminQueryHandler $requireAdmin,
        private DeleteMediaCommandHandler $deleteMedia,
    ) {
    }

    #[Route('/media/{id}', methods: ['DELETE'])]
    public function __invoke(string $id, Request $request): Response
    {
        $this->requireAdmin->handle(new RequireAdminQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $this->deleteMedia->handle(new DeleteMediaCommand($id));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
