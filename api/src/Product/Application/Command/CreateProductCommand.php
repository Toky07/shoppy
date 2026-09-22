<?php

declare(strict_types=1);

namespace App\Product\Application\Command;

final readonly class CreateProductCommand
{
    public function __construct(
        public string $name,
        public int $priceCents,
        public ?string $description = null,
        public int $stock = 0,
        public ?string $categoryId = null,
        public ?string $sku = null,
        /** @var list<array{id: string|null, sku: string, size: string|null, color: string|null, stock: int}>|null */
        public ?array $variants = null,
    ) {
    }
}
