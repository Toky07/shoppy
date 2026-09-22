<?php

declare(strict_types=1);

namespace App\Product\Application;

use App\Product\Domain\Exception\SkuAlreadyUsed;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductSku;

final readonly class UniqueProductSku
{
    public function __construct(private ProductRepository $products)
    {
    }

    public function allocate(ProductName $name, ?ProductId $except = null): ProductSku
    {
        $base = ProductSku::fromName($name);
        $candidate = $base;
        $copy = 2;

        while ($this->isTaken($candidate, $except)) {
            $candidate = $base->withCopyNumber($copy);
            ++$copy;
        }

        return $candidate;
    }

    public function assertFree(ProductSku $sku, ?ProductId $except = null): void
    {
        if ($this->isTaken($sku, $except)) {
            throw new SkuAlreadyUsed();
        }
    }

    private function isTaken(ProductSku $sku, ?ProductId $except): bool
    {
        $existing = $this->products->findBySku($sku);

        if ($existing === null) {
            return false;
        }

        return $except === null || $existing->id()->value() !== $except->value();
    }
}
