<?php

declare(strict_types=1);

namespace App\Order\Application;

use App\Order\Application\Response\CatalogSnapshot;
use App\Order\Domain\ValueObject\CatalogProductId;

interface Catalog
{
    public function findById(CatalogProductId $id, ?string $variantId = null): ?CatalogSnapshot;

    public function decreaseStock(CatalogProductId $id, int $quantity, ?string $variantId = null): void;

    public function increaseStock(CatalogProductId $id, int $quantity, ?string $variantId = null): void;
}
