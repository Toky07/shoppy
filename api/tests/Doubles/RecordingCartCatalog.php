<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Cart\Application\Catalog;
use App\Cart\Application\Response\CatalogSnapshot;
use App\Cart\Domain\ValueObject\CartProductId;

final class RecordingCartCatalog implements Catalog
{
    public int $findByIdCalls = 0;

    public int $findByItemsCalls = 0;

    public function __construct(private Catalog $inner)
    {
    }

    public function findById(CartProductId $id, ?string $variantId = null): ?CatalogSnapshot
    {
        $this->findByIdCalls++;

        return $this->inner->findById($id, $variantId);
    }

    public function findByItems(array $items): array
    {
        $this->findByItemsCalls++;

        return $this->inner->findByItems($items);
    }
}
