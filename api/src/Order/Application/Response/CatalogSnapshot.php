<?php

declare(strict_types=1);

namespace App\Order\Application\Response;

final readonly class CatalogSnapshot
{
    public function __construct(
        public string $id,
        public string $name,
        public int $unitPriceCents,
        public int $stock,
    ) {
    }
}
