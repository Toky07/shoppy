<?php

declare(strict_types=1);

namespace App\Product\Domain\Repository;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductListCriteria;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductSku;
use App\Product\Domain\ValueObject\ProductSlug;

interface ProductRepository
{
    public function save(Product $product): void;

    public function findById(ProductId $id): ?Product;

    public function findByName(ProductName $name): ?Product;

    public function findBySlug(ProductSlug $slug): ?Product;

    public function findBySku(ProductSku $sku): ?Product;

    /**
     * @param list<ProductId> $ids
     *
     * @return list<Product>
     */
    public function findByIds(array $ids): array;

    /**
     * @return list<Product>
     */
    public function findPage(int $offset, int $limit, ?ProductListCriteria $criteria = null): array;

    public function countAll(?ProductListCriteria $criteria = null): int;

    /**
     * Published products that share the category, excluding the product itself.
     *
     * @return list<Product>
     */
    public function findRelated(Product $product, int $limit): array;

    public function delete(Product $product): void;
}
