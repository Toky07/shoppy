<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductListCriteria;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductSku;
use App\Product\Domain\ValueObject\ProductSlug;
use App\Product\Domain\ValueObject\ProductSort;

final class InMemoryProductRepository implements ProductRepository
{
    /** @var array<string, Product> */
    private array $products = [];

    public function save(Product $product): void
    {
        $this->products[$product->id()->value()] = $product;
    }

    public function findById(ProductId $id): ?Product
    {
        return $this->products[$id->value()] ?? null;
    }

    public function findByName(ProductName $name): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->name()->value() === $name->value()) {
                return $product;
            }
        }

        return null;
    }

    public function findBySlug(ProductSlug $slug): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->slug()->value() === $slug->value()) {
                return $product;
            }
        }

        return null;
    }

    public function findBySku(ProductSku $sku): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->sku()->equals($sku)) {
                return $product;
            }

            foreach ($product->variants() as $variant) {
                if ($variant->sku()->equals($sku)) {
                    return $product;
                }
            }
        }

        return null;
    }

    public function findByIds(array $ids): array
    {
        $products = [];

        foreach ($ids as $id) {
            $product = $this->findById($id);

            if ($product !== null) {
                $products[] = $product;
            }
        }

        return $products;
    }

    public function findPage(int $offset, int $limit, ?ProductListCriteria $criteria = null): array
    {
        $criteria ??= ProductListCriteria::default();
        $products = $this->matching($criteria);
        $this->sort($products, $criteria->sort);

        return array_values(array_slice($products, $offset, $limit));
    }

    public function countAll(?ProductListCriteria $criteria = null): int
    {
        if ($criteria === null) {
            return count($this->products);
        }

        return count($this->matching($criteria));
    }

    /**
     * @return list<Product>
     */
    private function matching(ProductListCriteria $criteria): array
    {
        $products = array_values($this->products);

        return array_values(array_filter(
            $products,
            static function (Product $product) use ($criteria): bool {
                if ($criteria->search !== null) {
                    $needle = strtolower($criteria->search);
                    $description = $product->description()?->value();
                    $matchesSearch = str_contains(strtolower($product->name()->value()), $needle)
                        || ($description !== null && str_contains(strtolower($description), $needle));

                    if (!$matchesSearch) {
                        return false;
                    }
                }

                if ($criteria->minPriceCents !== null && $product->price()->cents() < $criteria->minPriceCents) {
                    return false;
                }

                if ($criteria->maxPriceCents !== null && $product->price()->cents() > $criteria->maxPriceCents) {
                    return false;
                }

                if ($criteria->categoryId !== null && $product->categoryId()?->value() !== $criteria->categoryId->value()) {
                    return false;
                }

                return !$criteria->inStockOnly || $product->stock()->value() > 0;
            },
        ));
    }

    /**
     * @param list<Product> $products
     */
    private function sort(array &$products, ProductSort $sort): void
    {
        usort(
            $products,
            static function (Product $left, Product $right) use ($sort): int {
                return match ($sort->value()) {
                    ProductSort::OLDEST => $left->createdAt() <=> $right->createdAt()
                        ?: $left->name()->value() <=> $right->name()->value(),
                    ProductSort::PRICE_ASC => $left->price()->cents() <=> $right->price()->cents()
                        ?: $left->name()->value() <=> $right->name()->value(),
                    ProductSort::PRICE_DESC => $right->price()->cents() <=> $left->price()->cents()
                        ?: $left->name()->value() <=> $right->name()->value(),
                    ProductSort::NAME_ASC => $left->name()->value() <=> $right->name()->value(),
                    default => $right->createdAt() <=> $left->createdAt()
                        ?: $left->name()->value() <=> $right->name()->value(),
                };
            },
        );
    }

    public function delete(Product $product): void
    {
        unset($this->products[$product->id()->value()]);
    }
}
