<?php

declare(strict_types=1);

namespace App\Product\Application\QueryHandler;

use App\Product\Application\Query\ListProductsQuery;
use App\Product\Application\Response\ProductListResponse;
use App\Product\Application\Response\ProductResponse;
use App\Product\Domain\Exception\InvalidProductPagination;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductListCriteria;

final readonly class ListProductsQueryHandler
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    public function handle(ListProductsQuery $query): ProductListResponse
    {
        if ($query->page < 1 || $query->limit < 1 || $query->limit > ListProductsQuery::MAX_LIMIT) {
            throw new InvalidProductPagination();
        }

        $offset = ($query->page - 1) * $query->limit;
        $criteria = ProductListCriteria::fromInput($query->search, $query->sort);
        $products = $this->productRepository->findPage($offset, $query->limit, $criteria);

        return new ProductListResponse(
            array_map(ProductResponse::fromProduct(...), $products),
            $query->page,
            $query->limit,
            $this->productRepository->countAll($criteria),
        );
    }
}
