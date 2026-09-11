<?php

declare(strict_types=1);

namespace App\Cart\Application;

use App\Cart\Application\Response\CatalogSnapshot;
use App\Cart\Domain\ValueObject\CartProductId;

interface Catalog
{
    public function findById(CartProductId $id): ?CatalogSnapshot;
}
