<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Product\Application\Query\ListRelatedProductsQuery;
use App\Product\Application\QueryHandler\ListRelatedProductsQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListRelatedProductsController
{
    public function __construct(private ListRelatedProductsQueryHandler $listRelatedProducts)
    {
    }

    #[Route('/products/{id}/related', methods: ['GET'])]
    public function __invoke(string $id): JsonResponse
    {
        return new JsonResponse($this->listRelatedProducts->handle(new ListRelatedProductsQuery($id))->toArray());
    }
}
