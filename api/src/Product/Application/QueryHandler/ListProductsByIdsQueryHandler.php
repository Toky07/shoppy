<?php

declare(strict_types=1);

namespace App\Product\Application\QueryHandler;

use App\Product\Application\ProductResponseFactory;
use App\Product\Application\Query\ListProductsByIdsQuery;
use App\Product\Application\Response\ProductListResponse;
use App\Product\Domain\Exception\InvalidProductId;
use App\Product\Domain\Exception\InvalidProductIdList;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;

final readonly class ListProductsByIdsQueryHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductResponseFactory $productResponseFactory,
    ) {
    }

    public function handle(ListProductsByIdsQuery $query): ProductListResponse
    {
        if (count($query->ids) > ListProductsByIdsQuery::MAX_IDS) {
            throw new InvalidProductIdList('At most 100 product ids can be requested.');
        }

        $ids = [];

        foreach ($query->ids as $id) {
            try {
                $ids[] = ProductId::fromString($id);
            } catch (InvalidProductId) {
                throw new InvalidProductIdList('Product id must be a valid UUID.');
            }
        }

        $products = $this->productRepository->findByIds($ids);
        $items = $this->productResponseFactory->fromProducts($products);

        return new ProductListResponse($items, 1, max(count($items), 1), count($items));
    }
}
