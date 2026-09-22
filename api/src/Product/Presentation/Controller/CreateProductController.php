<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Application\Query\RequireAdminQuery;
use App\Auth\Application\QueryHandler\RequireAdminQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\CommandHandler\CreateProductCommandHandler;
use App\Product\Application\Query\GetProductQuery;
use App\Product\Application\QueryHandler\GetProductQueryHandler;
use App\Product\Presentation\Request\CreateProductHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class CreateProductController
{
    public function __construct(
        private RequireAdminQueryHandler $requireAdmin,
        private CreateProductCommandHandler $createProduct,
        private GetProductQueryHandler $getProduct,
    ) {
    }

    #[Route('/products', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $this->requireAdmin->handle(new RequireAdminQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $httpRequest = CreateProductHttpRequest::fromPayload($request->toArray());

        $id = $this->createProduct->handle(new CreateProductCommand(
            name: $httpRequest->name,
            priceCents: $httpRequest->priceCents,
            description: $httpRequest->description,
            stock: $httpRequest->stock,
            categoryId: $httpRequest->categoryId,
            sku: $httpRequest->sku,
            variants: $httpRequest->variants,
        ));

        $product = $this->getProduct->handle(new GetProductQuery($id->value()));

        return new JsonResponse(
            $product->toArray(),
            Response::HTTP_CREATED,
            ['Location' => '/products/'.$product->slug],
        );
    }
}
