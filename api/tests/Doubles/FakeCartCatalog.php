<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Cart\Application\Catalog;
use App\Cart\Application\Response\CatalogSnapshot;
use App\Cart\Domain\ValueObject\CartProductId;

final class FakeCartCatalog implements Catalog
{
    /** @var array<string, CatalogSnapshot> */
    private array $items = [];

    public function add(CatalogSnapshot $item): void
    {
        $this->items[$item->id] = $item;
    }

    public function findById(CartProductId $id): ?CatalogSnapshot
    {
        return $this->items[$id->value()] ?? null;
    }
}
