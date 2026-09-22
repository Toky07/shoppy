<?php

declare(strict_types=1);

namespace App\Product\Domain\Entity;

use App\Product\Domain\Exception\InsufficientProductStock;
use App\Product\Domain\Exception\InvalidProductVariant;
use App\Product\Domain\ValueObject\ProductSku;
use App\Product\Domain\ValueObject\StockQuantity;
use App\Product\Domain\ValueObject\VariantId;
use App\Product\Domain\ValueObject\VariantLabel;

final class ProductVariant
{
    private function __construct(
        private VariantId $id,
        private ProductSku $sku,
        private ?VariantLabel $size,
        private ?VariantLabel $color,
        private StockQuantity $stock,
    ) {
    }

    public static function create(
        VariantId $id,
        ProductSku $sku,
        ?VariantLabel $size,
        ?VariantLabel $color,
        StockQuantity $stock,
    ): self {
        if ($size === null && $color === null) {
            throw new InvalidProductVariant('A variant needs a size or a color.');
        }

        return new self($id, $sku, $size, $color, $stock);
    }

    public function id(): VariantId
    {
        return $this->id;
    }

    public function sku(): ProductSku
    {
        return $this->sku;
    }

    public function size(): ?VariantLabel
    {
        return $this->size;
    }

    public function color(): ?VariantLabel
    {
        return $this->color;
    }

    public function stock(): StockQuantity
    {
        return $this->stock;
    }

    public function label(): string
    {
        $parts = [];

        if ($this->size !== null) {
            $parts[] = $this->size->value();
        }

        if ($this->color !== null) {
            $parts[] = $this->color->value();
        }

        return implode(' · ', $parts);
    }

    public function optionKey(): string
    {
        return mb_strtolower($this->size?->value() ?? '')."\0".mb_strtolower($this->color?->value() ?? '');
    }

    public function decreaseStock(int $quantity): void
    {
        if (!$this->stock->isAtLeast($quantity)) {
            throw new InsufficientProductStock($this->stock->value(), $quantity);
        }

        $this->stock = $this->stock->subtract($quantity);
    }

    public function increaseStock(int $quantity): void
    {
        $this->stock = $this->stock->add($quantity);
    }
}
