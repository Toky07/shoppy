<?php

declare(strict_types=1);

namespace App\Product\Application;

use App\Media\Domain\Entity\Media;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Product\Application\Response\ProductResponse;
use App\Product\Domain\Entity\Category;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\CategoryRepository;

final readonly class ProductResponseFactory
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function fromProduct(Product $product): ProductResponse
    {
        $items = $this->mediaRepository->findByOwner(
            MediaOwnerType::fromInput(MediaOwnerType::PRODUCT_ALIAS),
            MediaOwnerId::fromString($product->id()->value()),
        );

        return ProductResponse::fromProduct(
            $product,
            array_map(static fn (Media $media): string => $media->publicUrl(), $items),
            $this->categoryFor($product),
        );
    }

    /**
     * @param list<Product> $products
     *
     * @return list<ProductResponse>
     */
    public function fromProducts(array $products): array
    {
        if ($products === []) {
            return [];
        }

        $ownerIds = array_map(
            static fn (Product $product): MediaOwnerId => MediaOwnerId::fromString($product->id()->value()),
            $products,
        );
        $grouped = $this->mediaRepository->findByOwners(
            MediaOwnerType::fromInput(MediaOwnerType::PRODUCT_ALIAS),
            $ownerIds,
        );
        $categories = $this->categoriesFor($products);

        return array_map(
            static function (Product $product) use ($grouped, $categories): ProductResponse {
                $items = $grouped[$product->id()->value()] ?? [];

                return ProductResponse::fromProduct(
                    $product,
                    array_map(static fn (Media $media): string => $media->publicUrl(), $items),
                    $product->categoryId() === null ? null : ($categories[$product->categoryId()->value()] ?? null),
                );
            },
            $products,
        );
    }

    private function categoryFor(Product $product): ?Category
    {
        $categoryId = $product->categoryId();

        if ($categoryId === null) {
            return null;
        }

        return $this->categoryRepository->findById($categoryId);
    }

    /**
     * @param list<Product> $products
     *
     * @return array<string, Category>
     */
    private function categoriesFor(array $products): array
    {
        $ids = [];

        foreach ($products as $product) {
            $categoryId = $product->categoryId();

            if ($categoryId !== null) {
                $ids[$categoryId->value()] = $categoryId;
            }
        }

        $indexed = [];

        foreach ($this->categoryRepository->findByIds(array_values($ids)) as $category) {
            $indexed[$category->id()->value()] = $category;
        }

        return $indexed;
    }
}
