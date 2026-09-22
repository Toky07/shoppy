<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Product\Application\QueryHandler\ListCategoriesQueryHandler;
use App\Product\Application\Response\CategoryResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListCategoriesController
{
    public function __construct(private ListCategoriesQueryHandler $listCategories)
    {
    }

    #[Route('/categories', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'items' => array_map(
                static fn (CategoryResponse $category): array => $category->toArray(),
                $this->listCategories->handle(),
            ),
        ]);
    }
}
