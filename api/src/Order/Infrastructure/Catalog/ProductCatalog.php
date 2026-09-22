<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Catalog;

use App\Order\Application\Catalog;
use App\Order\Application\Response\CatalogSnapshot;
use App\Order\Domain\Exception\CatalogProductNotFound;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Exception\InsufficientProductStock;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\VariantId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ProductCatalog implements Catalog
{
    public function __construct(
        private ProductRepository $productRepository,
        private ?EntityManagerInterface $entityManager = null,
    ) {
    }

    public function findById(CatalogProductId $id, ?string $variantId = null): ?CatalogSnapshot
    {
        $product = $this->productRepository->findById(ProductId::fromString($id->value()));

        if ($product === null || !$product->isPublished()) {
            return null;
        }

        if ($variantId === null) {
            return new CatalogSnapshot(
                $product->id()->value(),
                $product->name()->value(),
                $product->price()->cents(),
                $product->stock()->value(),
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
        );
    }

    public function decreaseStock(CatalogProductId $id, int $quantity, ?string $variantId = null): void
    {
        $product = $this->requireProduct($id);
        $physical = $variantId === null
            ? $product->stock()->value()
            : ($product->findVariant(VariantId::fromString($variantId))?->stock()->value() ?? 0);
        $reserved = $this->reservedStock($variantId === null ? 'products' : 'product_variants', $variantId ?? $id->value());
        $available = $physical - $reserved;

        if ($available < $quantity) {
            throw new InsufficientProductStock(max(0, $available), $quantity);
        }

        $product->decreaseStock($quantity, $variantId === null ? null : VariantId::fromString($variantId));
        $this->productRepository->save($product);
    }

    public function increaseStock(CatalogProductId $id, int $quantity, ?string $variantId = null): void
    {
        $product = $this->requireProduct($id);
        $product->increaseStock($quantity, $variantId === null ? null : VariantId::fromString($variantId));
        $this->productRepository->save($product);
    }

    private function requireProduct(CatalogProductId $id): Product
    {
        $product = $this->productRepository->findById(ProductId::fromString($id->value()));

        if ($product === null) {
            throw new CatalogProductNotFound($id);
        }

        return $product;
    }

    private function reservedStock(string $table, string $id): int
    {
        if ($this->entityManager === null) {
            return 0;
        }

        $reserved = $this->entityManager->getConnection()->fetchOne(
            "SELECT reserved_stock FROM {$table} WHERE id = :id",
            ['id' => $id],
        );

        return (int) $reserved;
    }
}
