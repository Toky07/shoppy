<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Application\Query\RequireAdminQuery;
use App\Auth\Application\QueryHandler\RequireAdminQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Product\Application\Command\DeleteProductCommand;
use App\Product\Application\CommandHandler\DeleteProductCommandHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class DeleteProductController
{
    public function __construct(
        private RequireAdminQueryHandler $requireAdmin,
        private DeleteProductCommandHandler $deleteProduct,
    ) {
    }

    #[Route('/products/{id}', methods: ['DELETE'])]
    public function __invoke(string $id, Request $request): Response
    {
        $this->requireAdmin->handle(new RequireAdminQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $this->deleteProduct->handle(new DeleteProductCommand($id));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
