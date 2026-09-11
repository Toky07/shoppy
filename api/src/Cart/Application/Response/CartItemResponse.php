<?php

declare(strict_types=1);

namespace App\Cart\Application\Response;

final readonly class CartItemResponse
{
    public function __construct(
        public string $productId,
        public string $name,
        public int $quantity,
        public int $unitPriceCents,
        public int $lineTotalCents,
        public int $availableStock,
    ) {
    }

    /**
     * @return array{
     *     productId: string,
     *     name: string,
     *     quantity: int,
     *     unitPrice: array{cents: int, currency: string},
     *     lineTotal: array{cents: int, currency: string},
     *     availableStock: int
     * }
     */
    public function toArray(): array
    {
        return [
            'productId' => $this->productId,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unitPrice' => [
                'cents' => $this->unitPriceCents,
                'currency' => 'EUR',
            ],
            'lineTotal' => [
                'cents' => $this->lineTotalCents,
                'currency' => 'EUR',
            ],
            'availableStock' => $this->availableStock,
        ];
    }
}
