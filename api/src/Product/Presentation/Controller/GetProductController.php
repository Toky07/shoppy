<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Product\Application\Query\GetProductQuery;
use App\Product\Application\QueryHandler\GetProductQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetProductController
{
    public function __construct(private GetProductQueryHandler $getProduct)
    {
    }

    #[Route('/products/{id}', methods: ['GET'])]
    public function __invoke(string $id): JsonResponse
    {
        $product = $this->getProduct->handle(new GetProductQuery($id));

        return new JsonResponse($product->toArray());
    }
}
