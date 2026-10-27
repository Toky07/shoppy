<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductListCriteria;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductSku;
use App\Product\Domain\ValueObject\ProductSlug;

final class CountingProductRepository implements ProductRepository
{
    public int $findByIdCalls = 0;

    public int $findByIdsCalls = 0;

    public function __construct(private ProductRepository $inner)
    {
    }

    public function save(Product $product): void
    {
        $this->inner->save($product);
    }

    public function findById(ProductId $id): ?Product
    {
        $this->findByIdCalls++;

        return $this->inner->findById($id);
    }

    public function findByIdForUpdate(ProductId $id): ?Product
    {
        return $this->inner->findByIdForUpdate($id);
    }

    public function findByName(ProductName $name): ?Product
    {
        return $this->inner->findByName($name);
    }

    public function findBySlug(ProductSlug $slug): ?Product
    {
        return $this->inner->findBySlug($slug);
    }

    public function findBySku(ProductSku $sku): ?Product
    {
        return $this->inner->findBySku($sku);
    }

    public function findByIds(array $ids): array
    {
        $this->findByIdsCalls++;

        return $this->inner->findByIds($ids);
    }

    public function findPage(int $offset, int $limit, ?ProductListCriteria $criteria = null): array
    {
        return $this->inner->findPage($offset, $limit, $criteria);
    }

    public function countAll(?ProductListCriteria $criteria = null): int
    {
        return $this->inner->countAll($criteria);
    }

    public function findLowStock(int $maxStock, int $limit): array
    {
        return $this->inner->findLowStock($maxStock, $limit);
    }

    public function findRelated(Product $product, int $limit): array
    {
        return $this->inner->findRelated($product, $limit);
    }

    public function delete(Product $product): void
    {
        $this->inner->delete($product);
    }
}
