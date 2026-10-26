<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Catalog;

use App\Cart\Application\Catalog;
use App\Cart\Application\Response\CatalogSnapshot;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\VariantId;

final readonly class ProductCatalog implements Catalog
{
    public function __construct(
        private ProductRepository $productRepository,
        private MediaRepository $mediaRepository,
    ) {
    }

    public function findById(CartProductId $id, ?string $variantId = null): ?CatalogSnapshot
    {
        $product = $this->productRepository->findById(ProductId::fromString($id->value()));

        if ($product === null || !$product->isPublished()) {
            return null;
        }

        if ($variantId === null) {
            return $this->snapshot(
                $product,
                $product->name()->value(),
                $product->stock()->value(),
                $product->hasVariants(),
            );
        }

        $variant = $product->findVariant(VariantId::fromString($variantId));

        if ($variant === null) {
            return null;
        }

        return $this->snapshot(
            $product,
            $product->name()->value().' — '.$variant->label(),
            $variant->stock()->value(),
            true,
        );
    }

    private function snapshot(Product $product, string $name, int $stock, bool $requiresVariant): CatalogSnapshot
    {
        $media = $this->mediaRepository->findByOwner(
            MediaOwnerType::fromInput(MediaOwnerType::PRODUCT_ALIAS),
            MediaOwnerId::fromString($product->id()->value()),
        );

        return new CatalogSnapshot(
            $product->id()->value(),
            $name,
            $product->price()->cents(),
            $stock,
            $requiresVariant,
            ($media[0] ?? null)?->publicUrl(),
        );
    }
}
