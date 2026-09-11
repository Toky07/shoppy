<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Product\Application\Query\ListProductsQuery;
use App\Product\Application\QueryHandler\ListProductsQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListProductsController
{
    public function __construct(private ListProductsQueryHandler $listProducts)
    {
    }

    #[Route('/products', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $response = $this->listProducts->handle(new ListProductsQuery(
            page: $request->query->getInt('page', ListProductsQuery::DEFAULT_PAGE),
            limit: $request->query->getInt('limit', ListProductsQuery::DEFAULT_LIMIT),
            search: self::queryString($request, 'q'),
            sort: self::queryString($request, 'sort'),
        ));

        return new JsonResponse($response->toArray());
    }

    private static function queryString(Request $request, string $key): ?string
    {
        $value = $request->query->get($key);

        return is_string($value) ? $value : null;
    }
}
