<?php

declare(strict_types=1);

namespace App\Product\Application\QueryHandler;

use App\Product\Application\Query\GetProductQuery;
use App\Product\Application\Response\ProductResponse;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;

final readonly class GetProductQueryHandler
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    public function handle(GetProductQuery $query): ProductResponse
    {
        $id = ProductId::fromString($query->id);
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            throw new ProductNotFound($id);
        }

        return ProductResponse::fromProduct($product);
    }
}
