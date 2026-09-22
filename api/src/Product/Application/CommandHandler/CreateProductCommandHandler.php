<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\ProductVariants;
use App\Product\Application\UniqueProductSku;
use App\Product\Application\UniqueProductSlug;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Exception\CategoryNotFound;
use App\Product\Domain\Repository\CategoryRepository;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\ProductSku;
use App\Product\Domain\ValueObject\StockQuantity;
use App\Shared\Domain\Clock;

final readonly class CreateProductCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private Clock $clock,
        private UniqueProductSlug $uniqueProductSlug,
        private CategoryRepository $categoryRepository,
        private UniqueProductSku $uniqueProductSku,
    ) {
    }

    public function handle(CreateProductCommand $command): ProductId
    {
        $name = ProductName::fromString($command->name);
        $sku = $command->sku === null
            ? $this->uniqueProductSku->allocate($name)
            : ProductSku::fromString($command->sku);
        $this->uniqueProductSku->assertFree($sku);
        $variants = ProductVariants::fromInput($command->variants ?? []);

        foreach ($variants as $variant) {
            $this->uniqueProductSku->assertFree($variant->sku());
        }

        $product = Product::create(
            ProductId::generate(),
            $name,
            ProductPrice::fromCents($command->priceCents),
            $this->clock->now(),
            self::descriptionFrom($command->description),
            StockQuantity::fromInt($command->stock),
            $this->uniqueProductSlug->allocate($name),
            self::categoryFrom($command->categoryId, $this->categoryRepository),
            $sku,
            $variants,
            $command->published,
        );

        $this->productRepository->save($product);

        return $product->id();
    }

    private static function descriptionFrom(?string $description): ?ProductDescription
    {
        if ($description === null) {
            return null;
        }

        return ProductDescription::fromString($description);
    }

    private static function categoryFrom(?string $categoryId, CategoryRepository $categories): ?CategoryId
    {
        if ($categoryId === null) {
            return null;
        }

        $id = CategoryId::fromString($categoryId);

        if ($categories->findById($id) === null) {
            throw new CategoryNotFound($id->value());
        }

        return $id;
    }
}
