<?php

declare(strict_types=1);

namespace App\Product\Application\Command;

final readonly class SetProductStockCommand
{
    public function __construct(
        public string $id,
        public int $stock,
    ) {
    }
}
