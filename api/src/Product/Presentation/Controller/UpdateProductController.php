<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Application\Query\RequireAdminQuery;
use App\Auth\Application\QueryHandler\RequireAdminQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Product\Application\Command\UpdateProductCommand;
use App\Product\Application\CommandHandler\UpdateProductCommandHandler;
use App\Product\Application\Query\GetProductQuery;
use App\Product\Application\QueryHandler\GetProductQueryHandler;
use App\Product\Presentation\Request\UpdateProductHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class UpdateProductController
{
    public function __construct(
        private RequireAdminQueryHandler $requireAdmin,
        private UpdateProductCommandHandler $updateProduct,
        private GetProductQueryHandler $getProduct,
    ) {
    }

    #[Route('/products/{id}', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $this->requireAdmin->handle(new RequireAdminQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $httpRequest = UpdateProductHttpRequest::fromPayload($request->toArray());

        $this->updateProduct->handle(new UpdateProductCommand(
            id: $id,
            name: $httpRequest->name,
            priceCents: $httpRequest->priceCents,
            descriptionProvided: $httpRequest->descriptionProvided,
            description: $httpRequest->description,
            categoryProvided: $httpRequest->categoryProvided,
            categoryId: $httpRequest->categoryId,
            skuProvided: $httpRequest->skuProvided,
            sku: $httpRequest->sku,
            variantsProvided: $httpRequest->variantsProvided,
            variants: $httpRequest->variants,
            publishedProvided: $httpRequest->publishedProvided,
            published: $httpRequest->published,
        ));

        $product = $this->getProduct->handle(new GetProductQuery($id));

        return new JsonResponse($product->toArray());
    }
}
