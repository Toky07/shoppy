<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Catalog;

use App\Cart\Application\Catalog;
use App\Cart\Application\Response\CatalogSnapshot;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\VariantId;

final readonly class ProductCatalog implements Catalog
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    public function findById(CartProductId $id, ?string $variantId = null): ?CatalogSnapshot
    {
        $product = $this->productRepository->findById(ProductId::fromString($id->value()));

        if ($product === null) {
            return null;
        }

        if ($variantId === null) {
            return new CatalogSnapshot(
                $product->id()->value(),
                $product->name()->value(),
                $product->price()->cents(),
                $product->stock()->value(),
                $product->hasVariants(),
            );
        }

        $variant = $product->findVariant(VariantId::fromString($variantId));

        if ($variant === null) {
            return null;
        }

        return new CatalogSnapshot(
            $product->id()->value(),
            $product->name()->value().' — '.$variant->label(),
            $product->price()->cents(),
            $variant->stock()->value(),
            true,
        );
    }
}
