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

    public function findById(CartProductId $id, ?string $variantId = null): ?CatalogSnapshot
    {
        return $this->items[$id->value()] ?? null;
    }

    public function findByItems(array $items): array
    {
        $snapshots = [];

        foreach ($items as $item) {
            $snapshot = $this->findById($item->productId(), $item->variantId()?->value());

            if ($snapshot !== null) {
                $snapshots[$item->lineKey()] = $snapshot;
            }
        }

        return $snapshots;
    }
}
