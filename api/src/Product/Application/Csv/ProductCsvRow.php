<?php

declare(strict_types=1);

namespace App\Product\Application\Csv;

final readonly class ProductCsvRow
{
    /**
     * @param list<string> $images
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public int $priceCents,
        public int $stock,
        public array $images,
    ) {
    }
}
