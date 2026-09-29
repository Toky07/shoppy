<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Product\Application\Command\UpdateProductCommand;
use App\Product\Application\CommandHandler\UpdateProductCommandHandler;
use App\Product\Application\Query\GetProductQuery;
use App\Product\Application\QueryHandler\GetProductQueryHandler;
use App\Product\Presentation\Request\UpdateProductHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class UpdateProductController
{
    public function __construct(
        private UpdateProductCommandHandler $updateProduct,
        private GetProductQueryHandler $getProduct,
    ) {
    }

    #[Route('/products/{id}', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(string $id, Request $request): JsonResponse
    {
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
