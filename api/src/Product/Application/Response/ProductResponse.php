<?php

declare(strict_types=1);

namespace App\Product\Application\Response;

use App\Product\Domain\Entity\Category;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Entity\ProductVariant;
use DateTimeInterface;

final readonly class ProductResponse
{
    /**
     * @param list<string> $imageUrls
     * @param list<array{id: string, sku: string, size: string|null, color: string|null, stock: int}> $variants
     */
    public function __construct(
        public string $id,
        public string $slug,
        public string $name,
        public int $priceCents,
        public string $currency,
        public string $createdAt,
        public ?string $description,
        public int $stock,
        public ?string $imageUrl,
        public array $imageUrls,
        public ?CategoryResponse $category,
        public string $sku,
        public array $variants,
    ) {
    }

    /**
     * @param list<string> $imageUrls
     */
    public static function fromProduct(Product $product, array $imageUrls = [], ?Category $category = null): self
    {
        return new self(
            $product->id()->value(),
            $product->slug()->value(),
            $product->name()->value(),
            $product->price()->cents(),
            $product->price()->currency(),
            $product->createdAt()->format(DateTimeInterface::ATOM),
            $product->description()?->value(),
            $product->stock()->value(),
            $imageUrls[0] ?? null,
            array_values($imageUrls),
            $category === null ? null : CategoryResponse::fromCategory($category),
            $product->sku()->value(),
            array_map(self::variantToArray(...), $product->variants()),
        );
    }

    /**
     * @return array{id: string, sku: string, size: string|null, color: string|null, stock: int}
     */
    private static function variantToArray(ProductVariant $variant): array
    {
        return [
            'id' => $variant->id()->value(),
            'sku' => $variant->sku()->value(),
            'size' => $variant->size()?->value(),
            'color' => $variant->color()?->value(),
            'stock' => $variant->stock()->value(),
        ];
    }

    /**
     * @return array{
     *     id: string,
     *     slug: string,
     *     name: string,
     *     description: string|null,
     *     price: array{cents: int, currency: string},
     *     stock: int,
     *     imageUrl: string|null,
     *     imageUrls: list<string>,
     *     createdAt: string,
     *     category: array{id: string, name: string, slug: string}|null,
     *     sku: string,
     *     variants: list<array{id: string, sku: string, size: string|null, color: string|null, stock: int}>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'price' => [
                'cents' => $this->priceCents,
                'currency' => $this->currency,
            ],
            'stock' => $this->stock,
            'imageUrl' => $this->imageUrl,
            'imageUrls' => $this->imageUrls,
            'createdAt' => $this->createdAt,
            'category' => $this->category?->toArray(),
            'sku' => $this->sku,
            'variants' => $this->variants,
        ];
    }
}
