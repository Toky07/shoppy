<?php

declare(strict_types=1);

namespace App\Product\Application\QueryHandler;

use App\Product\Application\ProductResponseFactory;
use App\Product\Application\Query\ListProductsQuery;
use App\Product\Application\Response\ProductListResponse;
use App\Product\Domain\Exception\InvalidProductPagination;
use App\Product\Domain\Repository\CategoryRepository;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\CategorySlug;
use App\Product\Domain\ValueObject\ProductListCriteria;

final readonly class ListProductsQueryHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductResponseFactory $productResponseFactory,
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function handle(ListProductsQuery $query): ProductListResponse
    {
        if ($query->page < 1 || $query->limit < 1 || $query->limit > ListProductsQuery::MAX_LIMIT) {
            throw new InvalidProductPagination();
        }

        $categoryId = null;

        if ($query->categorySlug !== null && trim($query->categorySlug) !== '') {
            $category = $this->categoryRepository->findBySlug(CategorySlug::fromString(trim($query->categorySlug)));

            if ($category === null) {
                return new ProductListResponse([], $query->page, $query->limit, 0);
            }

            $categoryId = $category->id();
        }

        $offset = ($query->page - 1) * $query->limit;
        $criteria = ProductListCriteria::fromInput(
            $query->search,
            $query->sort,
            $query->minPriceCents,
            $query->maxPriceCents,
            $query->inStockOnly,
            $categoryId,
            publishedOnly: !$query->includeDrafts,
        );
        $products = $this->productRepository->findPage($offset, $query->limit, $criteria);

        return new ProductListResponse(
            $this->productResponseFactory->fromProducts($products),
            $query->page,
            $query->limit,
            $this->productRepository->countAll($criteria),
        );
    }
}
