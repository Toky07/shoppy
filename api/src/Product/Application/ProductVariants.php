<?php

declare(strict_types=1);

namespace App\Product\Application;

use App\Product\Domain\Entity\ProductVariant;
use App\Product\Domain\ValueObject\ProductSku;
use App\Product\Domain\ValueObject\StockQuantity;
use App\Product\Domain\ValueObject\VariantId;
use App\Product\Domain\ValueObject\VariantLabel;

final class ProductVariants
{
    /**
     * @param list<array{id: string|null, sku: string, size: string|null, color: string|null, stock: int}> $rows
     *
     * @return list<ProductVariant>
     */
    public static function fromInput(array $rows): array
    {
        $variants = [];

        foreach ($rows as $row) {
            $variants[] = ProductVariant::create(
                $row['id'] === null ? VariantId::generate() : VariantId::fromString($row['id']),
                ProductSku::fromString($row['sku']),
                $row['size'] === null ? null : VariantLabel::fromString($row['size'], 'size'),
                $row['color'] === null ? null : VariantLabel::fromString($row['color'], 'color'),
                StockQuantity::fromInt($row['stock']),
            );
        }

        return $variants;
    }
}
