<?php

declare(strict_types=1);

namespace App\Product\Application\QueryHandler;

use App\Product\Application\ProductResponseFactory;
use App\Product\Application\Query\ListRelatedProductsQuery;
use App\Product\Application\Response\ProductListResponse;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Exception\InvalidProductSlug;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductSlug;

final readonly class ListRelatedProductsQueryHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductResponseFactory $productResponseFactory,
    ) {
    }

    public function handle(ListRelatedProductsQuery $query): ProductListResponse
    {
        $product = $this->findPublished($query->productId);

        if ($product === null) {
            throw new ProductNotFound($query->productId);
        }

        $related = $this->productRepository->findRelated($product, ListRelatedProductsQuery::LIMIT);

        return new ProductListResponse(
            $this->productResponseFactory->fromProducts($related),
            1,
            ListRelatedProductsQuery::LIMIT,
            count($related),
        );
    }

    private function findPublished(string $reference): ?Product
    {
        $product = null;

        if (ProductId::isValid($reference)) {
            $product = $this->productRepository->findById(ProductId::fromString($reference));
        }

        if ($product === null) {
            try {
                $product = $this->productRepository->findBySlug(ProductSlug::fromString($reference));
            } catch (InvalidProductSlug) {
                $product = null;
            }
        }

        if ($product === null || !$product->isPublished()) {
            return null;
        }

        return $product;
    }
}
