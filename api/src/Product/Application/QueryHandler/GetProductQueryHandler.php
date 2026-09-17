<?php

declare(strict_types=1);

namespace App\Product\Application\QueryHandler;

use App\Product\Application\ProductResponseFactory;
use App\Product\Application\Query\GetProductQuery;
use App\Product\Application\Response\ProductResponse;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Exception\InvalidProductSlug;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductSlug;

final readonly class GetProductQueryHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductResponseFactory $productResponseFactory,
    ) {
    }

    public function handle(GetProductQuery $query): ProductResponse
    {
        $product = $this->find($query->id);

        if ($product === null) {
            throw new ProductNotFound($query->id);
        }

        return $this->productResponseFactory->fromProduct($product);
    }

    private function find(string $reference): ?Product
    {
        if (ProductId::isValid($reference)) {
            $byId = $this->productRepository->findById(ProductId::fromString($reference));

            if ($byId !== null) {
                return $byId;
            }
        }

        try {
            return $this->productRepository->findBySlug(ProductSlug::fromString($reference));
        } catch (InvalidProductSlug) {
            return null;
        }
    }
}
