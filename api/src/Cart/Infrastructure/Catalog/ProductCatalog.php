<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Catalog;

use App\Cart\Application\Catalog;
use App\Cart\Application\Response\CatalogSnapshot;
use App\Cart\Domain\ValueObject\CartItem;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartQuantity;
use App\Cart\Domain\ValueObject\CartVariantId;
use App\Media\Domain\Entity\Media;
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
        $item = CartItem::of(
            $id,
            CartQuantity::fromInt(1),
            $variantId === null ? null : CartVariantId::fromString($variantId),
        );

        return $this->findByItems([$item])[$item->lineKey()] ?? null;
    }

    public function findByItems(array $items): array
    {
        if ($items === []) {
            return [];
        }

        $productIds = [];

        foreach ($items as $item) {
            $productIds[$item->productId()->value()] = ProductId::fromString($item->productId()->value());
        }

        $products = [];

        foreach ($this->productRepository->findByIds(array_values($productIds)) as $product) {
            $products[$product->id()->value()] = $product;
        }

        $mediaByOwner = $products === [] ? [] : $this->mediaRepository->findByOwners(
            MediaOwnerType::fromInput(MediaOwnerType::PRODUCT_ALIAS),
            array_map(
                static fn (Product $product): MediaOwnerId => MediaOwnerId::fromString($product->id()->value()),
                array_values($products),
            ),
        );

        $snapshots = [];

        foreach ($items as $item) {
            $product = $products[$item->productId()->value()] ?? null;

            if ($product === null || !$product->isPublished()) {
                continue;
            }

            $snapshot = $this->snapshotFor(
                $product,
                $item->variantId()?->value(),
                $mediaByOwner[$product->id()->value()] ?? [],
            );

            if ($snapshot !== null) {
                $snapshots[$item->lineKey()] = $snapshot;
            }
        }

        return $snapshots;
    }

    /**
     * @param list<Media> $media
     */
    private function snapshotFor(Product $product, ?string $variantId, array $media): ?CatalogSnapshot
    {
        if ($variantId === null) {
            return $this->snapshot(
                $product,
                $product->name()->value(),
                $product->stock()->value(),
                $product->hasVariants(),
                $media,
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
            $media,
        );
    }

    /**
     * @param list<Media> $media
     */
    private function snapshot(Product $product, string $name, int $stock, bool $requiresVariant, array $media): CatalogSnapshot
    {
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
