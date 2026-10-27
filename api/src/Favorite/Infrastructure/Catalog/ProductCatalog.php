<?php

declare(strict_types=1);

namespace App\Favorite\Infrastructure\Catalog;

use App\Favorite\Application\Catalog;
use App\Favorite\Domain\ValueObject\FavoriteProductId;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;

final readonly class ProductCatalog implements Catalog
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    public function isPublished(FavoriteProductId $id): bool
    {
        $product = $this->productRepository->findById(ProductId::fromString($id->value()));

        return $product !== null && $product->isPublished();
    }

    public function publishedAmong(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $productIds = [];

        foreach ($ids as $id) {
            $productIds[$id->value()] = ProductId::fromString($id->value());
        }

        $products = [];

        foreach ($this->productRepository->findByIds(array_values($productIds)) as $product) {
            $products[$product->id()->value()] = $product;
        }

        $published = [];

        foreach ($ids as $id) {
            $product = $products[$id->value()] ?? null;

            if ($product instanceof Product && $product->isPublished()) {
                $published[] = $id;
            }
        }

        return $published;
    }
}
