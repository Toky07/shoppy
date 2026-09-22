<?php

declare(strict_types=1);

namespace App\Product\Domain\Entity;

use App\Product\Domain\Exception\InsufficientProductStock;
use App\Product\Domain\Exception\InvalidProductVariant;
use App\Product\Domain\Exception\ProductStockManagedByVariants;
use App\Product\Domain\Exception\VariantNotFound;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\ProductSku;
use App\Product\Domain\ValueObject\ProductSlug;
use App\Product\Domain\ValueObject\StockQuantity;
use App\Product\Domain\ValueObject\VariantId;
use DateTimeImmutable;

final class Product
{
    /**
     * @param list<ProductVariant> $variants
     */
    private function __construct(
        private ProductId $id,
        private ProductName $name,
        private ProductPrice $price,
        private DateTimeImmutable $createdAt,
        private ?ProductDescription $description,
        private StockQuantity $stock,
        private ProductSlug $slug,
        private ?CategoryId $categoryId,
        private ProductSku $sku,
        private array $variants,
    ) {
    }

    /**
     * @param list<ProductVariant> $variants
     */
    public static function create(
        ProductId $id,
        ProductName $name,
        ProductPrice $price,
        DateTimeImmutable $createdAt,
        ?ProductDescription $description = null,
        ?StockQuantity $stock = null,
        ?ProductSlug $slug = null,
        ?CategoryId $categoryId = null,
        ?ProductSku $sku = null,
        array $variants = [],
    ): self {
        $sku ??= ProductSku::fromName($name);
        self::assertVariants($sku, $variants);

        return new self(
            $id,
            $name,
            $price,
            $createdAt,
            $description,
            $variants === [] ? ($stock ?? StockQuantity::zero()) : StockQuantity::fromInt(self::sum($variants)),
            $slug ?? ProductSlug::fromName($name),
            $categoryId,
            $sku,
            array_values($variants),
        );
    }

    public function id(): ProductId
    {
        return $this->id;
    }

    public function name(): ProductName
    {
        return $this->name;
    }

    public function price(): ProductPrice
    {
        return $this->price;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function description(): ?ProductDescription
    {
        return $this->description;
    }

    public function stock(): StockQuantity
    {
        return $this->stock;
    }

    public function slug(): ProductSlug
    {
        return $this->slug;
    }

    public function categoryId(): ?CategoryId
    {
        return $this->categoryId;
    }

    public function sku(): ProductSku
    {
        return $this->sku;
    }

    /**
     * @return list<ProductVariant>
     */
    public function variants(): array
    {
        return $this->variants;
    }

    public function hasVariants(): bool
    {
        return $this->variants !== [];
    }

    public function findVariant(VariantId $id): ?ProductVariant
    {
        foreach ($this->variants as $variant) {
            if ($variant->id()->equals($id)) {
                return $variant;
            }
        }

        return null;
    }

    public function assignCategory(?CategoryId $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function rename(ProductName $name): void
    {
        $this->name = $name;
    }

    public function changeSlug(ProductSlug $slug): void
    {
        $this->slug = $slug;
    }

    public function changeSku(ProductSku $sku): void
    {
        self::assertVariants($sku, $this->variants);
        $this->sku = $sku;
    }

    /**
     * @param list<ProductVariant> $variants
     */
    public function replaceVariants(array $variants): void
    {
        $variants = array_values($variants);
        self::assertVariants($this->sku, $variants);
        $this->variants = $variants;

        if ($variants !== []) {
            $this->stock = StockQuantity::fromInt(self::sum($variants));
        }
    }

    public function changePrice(ProductPrice $price): void
    {
        $this->price = $price;
    }

    public function changeDescription(?ProductDescription $description): void
    {
        $this->description = $description;
    }

    public function setStock(StockQuantity $stock): void
    {
        if ($this->variants !== []) {
            throw new ProductStockManagedByVariants();
        }

        $this->stock = $stock;
    }

    public function decreaseStock(int $quantity, ?VariantId $variantId = null): void
    {
        if ($variantId !== null) {
            $variant = $this->requireVariant($variantId);
            $variant->decreaseStock($quantity);
            $this->refreshStock();

            return;
        }

        if (!$this->stock->isAtLeast($quantity)) {
            throw new InsufficientProductStock($this->stock->value(), $quantity);
        }

        if ($this->variants === []) {
            $this->stock = $this->stock->subtract($quantity);

            return;
        }

        $remaining = $quantity;

        foreach ($this->variants as $variant) {
            $available = $variant->stock()->value();

            if ($available === 0) {
                continue;
            }

            $take = min($available, $remaining);
            $variant->decreaseStock($take);
            $remaining -= $take;

            if ($remaining === 0) {
                break;
            }
        }

        $this->refreshStock();
    }

    public function increaseStock(int $quantity, ?VariantId $variantId = null): void
    {
        if ($quantity < 1) {
            return;
        }

        if ($variantId !== null) {
            $this->requireVariant($variantId)->increaseStock($quantity);
            $this->refreshStock();

            return;
        }

        if ($this->variants === []) {
            $this->stock = $this->stock->add($quantity);

            return;
        }

        $this->variants[0]->increaseStock($quantity);
        $this->refreshStock();
    }

    private function requireVariant(VariantId $variantId): ProductVariant
    {
        $variant = $this->findVariant($variantId);

        if ($variant === null) {
            throw new VariantNotFound($variantId->value());
        }

        return $variant;
    }

    private function refreshStock(): void
    {
        if ($this->variants === []) {
            return;
        }

        $this->stock = StockQuantity::fromInt(self::sum($this->variants));
    }

    /**
     * @param list<ProductVariant> $variants
     */
    private static function assertVariants(ProductSku $productSku, array $variants): void
    {
        $skus = [$productSku->value() => true];
        $options = [];

        foreach ($variants as $variant) {
            $sku = $variant->sku()->value();

            if (isset($skus[$sku])) {
                throw new InvalidProductVariant('Variant SKU must be unique.');
            }

            $option = $variant->optionKey();

            if (isset($options[$option])) {
                throw new InvalidProductVariant('Variant options must be unique.');
            }

            $skus[$sku] = true;
            $options[$option] = true;
        }
    }

    /**
     * @param list<ProductVariant> $variants
     */
    private static function sum(array $variants): int
    {
        $total = 0;

        foreach ($variants as $variant) {
            $total += $variant->stock()->value();
        }

        return $total;
    }
}
