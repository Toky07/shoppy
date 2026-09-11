<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Order\Application\Catalog;
use App\Order\Application\Response\CatalogSnapshot;
use App\Order\Domain\Exception\CatalogProductNotFound;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Product\Domain\Exception\InsufficientProductStock;

final class FakeCatalog implements Catalog
{
    /** @var array<string, CatalogSnapshot> */
    private array $items = [];

    public function add(CatalogSnapshot $item): void
    {
        $this->items[$item->id] = $item;
    }

    public function findById(CatalogProductId $id): ?CatalogSnapshot
    {
        return $this->items[$id->value()] ?? null;
    }

    public function decreaseStock(CatalogProductId $id, int $quantity): void
    {
        $item = $this->items[$id->value()] ?? null;

        if ($item === null) {
            throw new CatalogProductNotFound($id);
        }

        if ($item->stock < $quantity) {
            throw new InsufficientProductStock($item->stock, $quantity);
        }

        $this->items[$id->value()] = new CatalogSnapshot(
            $item->id,
            $item->name,
            $item->unitPriceCents,
            $item->stock - $quantity,
        );
    }

    public function increaseStock(CatalogProductId $id, int $quantity): void
    {
        $item = $this->items[$id->value()] ?? null;

        if ($item === null) {
            throw new CatalogProductNotFound($id);
        }

        $this->items[$id->value()] = new CatalogSnapshot(
            $item->id,
            $item->name,
            $item->unitPriceCents,
            $item->stock + $quantity,
        );
    }
}
