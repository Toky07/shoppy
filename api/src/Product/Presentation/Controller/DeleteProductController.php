<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Product\Application\Command\DeleteProductCommand;
use App\Product\Application\CommandHandler\DeleteProductCommandHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class DeleteProductController
{
    public function __construct(
        private DeleteProductCommandHandler $deleteProduct,
    ) {
    }

    #[Route('/products/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(string $id, Request $request): Response
    {
        $this->deleteProduct->handle(new DeleteProductCommand($id));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
