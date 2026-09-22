<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Application\Query\RequireAdminQuery;
use App\Auth\Application\QueryHandler\RequireAdminQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Product\Application\Query\ListProductsByIdsQuery;
use App\Product\Application\Query\ListProductsQuery;
use App\Product\Application\QueryHandler\ListProductsByIdsQueryHandler;
use App\Product\Application\QueryHandler\ListProductsQueryHandler;
use App\Shared\Presentation\Exception\InvalidRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListProductsController
{
    public function __construct(
        private ListProductsQueryHandler $listProducts,
        private ListProductsByIdsQueryHandler $listProductsByIds,
        private RequireAdminQueryHandler $requireAdmin,
    ) {
    }

    #[Route('/products', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $ids = self::queryIds($request);

        if ($ids !== null) {
            return new JsonResponse($this->listProductsByIds->handle(new ListProductsByIdsQuery($ids))->toArray());
        }

        $includeDrafts = self::queryFlag($request, 'includeDrafts');

        if ($includeDrafts) {
            $this->requireAdmin->handle(new RequireAdminQuery(
                BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
            ));
        }

        $response = $this->listProducts->handle(new ListProductsQuery(
            page: $request->query->getInt('page', ListProductsQuery::DEFAULT_PAGE),
            limit: $request->query->getInt('limit', ListProductsQuery::DEFAULT_LIMIT),
            search: self::queryString($request, 'q'),
            sort: self::queryString($request, 'sort'),
            minPriceCents: self::queryCents($request, 'minPrice'),
            maxPriceCents: self::queryCents($request, 'maxPrice'),
            inStockOnly: self::queryFlag($request, 'inStock'),
            categorySlug: self::queryString($request, 'category'),
            includeDrafts: $includeDrafts,
        ));

        return new JsonResponse($response->toArray());
    }

    private static function queryString(Request $request, string $key): ?string
    {
        $value = $request->query->get($key);

        return is_string($value) ? $value : null;
    }

    private static function queryCents(Request $request, string $key): ?int
    {
        if (!$request->query->has($key) || $request->query->get($key) === '') {
            return null;
        }

        $value = $request->query->get($key);

        if (is_int($value) || (is_string($value) && preg_match('/^\d{1,9}$/', $value) === 1)) {
            return (int) $value;
        }

        throw InvalidRequest::of('This value must be an integer.', $key);
    }

    private static function queryFlag(Request $request, string $key): bool
    {
        if (!$request->query->has($key) || $request->query->get($key) === '') {
            return false;
        }

        $value = $request->query->get($key);

        if ($value === '1' || $value === 'true' || $value === 1 || $value === true) {
            return true;
        }

        if ($value === '0' || $value === 'false' || $value === 0 || $value === false) {
            return false;
        }

        throw InvalidRequest::of('This value must be a boolean.', $key);
    }

    /**
     * @return list<string>|null
     */
    private static function queryIds(Request $request): ?array
    {
        if (!$request->query->has('ids')) {
            return null;
        }

        $value = $request->query->get('ids');

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(trim(...), explode(',', $value)),
            static fn (string $id): bool => $id !== '',
        ));
    }
}
