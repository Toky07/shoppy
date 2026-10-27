<?php

declare(strict_types=1);

namespace App\Order\Application\Response;

final readonly class LowStockProductResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public int $stock,
    ) {
    }

    /**
     * @return array{id: string, name: string, slug: string, stock: int}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'stock' => $this->stock,
        ];
    }
}
