<?php

declare(strict_types=1);

namespace App\Product\Application\Command;

final readonly class UpdateProductCommand
{
    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?int $priceCents = null,
        public bool $descriptionProvided = false,
        public ?string $description = null,
        public bool $categoryProvided = false,
        public ?string $categoryId = null,
        public bool $skuProvided = false,
        public ?string $sku = null,
        public bool $variantsProvided = false,
        /** @var list<array{id: string|null, sku: string, size: string|null, color: string|null, stock: int}> */
        public array $variants = [],
    ) {
    }
}
