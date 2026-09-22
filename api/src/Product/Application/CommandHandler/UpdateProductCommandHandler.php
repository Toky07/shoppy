<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\UpdateProductCommand;
use App\Product\Application\ProductVariants;
use App\Product\Application\UniqueProductSku;
use App\Product\Application\UniqueProductSlug;
use App\Product\Domain\Exception\CategoryNotFound;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Repository\CategoryRepository;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\ProductSku;

final readonly class UpdateProductCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private UniqueProductSlug $uniqueProductSlug,
        private CategoryRepository $categoryRepository,
        private UniqueProductSku $uniqueProductSku,
    ) {
    }

    public function handle(UpdateProductCommand $command): void
    {
        $id = ProductId::fromString($command->id);
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            throw new ProductNotFound($id->value());
        }

        if ($command->name !== null) {
            $name = ProductName::fromString($command->name);
            $product->rename($name);
            $product->changeSlug($this->uniqueProductSlug->allocate($name, $id));
        }

        if ($command->priceCents !== null) {
            $product->changePrice(ProductPrice::fromCents($command->priceCents));
        }

        if ($command->descriptionProvided) {
            $product->changeDescription(
                $command->description === null ? null : ProductDescription::fromString($command->description),
            );
        }

        if ($command->categoryProvided) {
            $product->assignCategory($this->categoryFrom($command->categoryId));
        }

        if ($command->skuProvided && $command->sku !== null) {
            $sku = ProductSku::fromString($command->sku);
            $this->uniqueProductSku->assertFree($sku, $product->id());
            $product->changeSku($sku);
        }

        if ($command->publishedProvided) {
            $product->changePublication($command->published);
        }

        if ($command->variantsProvided) {
            $variants = ProductVariants::fromInput($command->variants);

            foreach ($variants as $variant) {
                $this->uniqueProductSku->assertFree($variant->sku(), $product->id());
            }

            $product->replaceVariants($variants);
        }

        $this->productRepository->save($product);
    }

    private function categoryFrom(?string $categoryId): ?CategoryId
    {
        if ($categoryId === null) {
            return null;
        }

        $id = CategoryId::fromString($categoryId);

        if ($this->categoryRepository->findById($id) === null) {
            throw new CategoryNotFound($id->value());
        }

        return $id;
    }
}
