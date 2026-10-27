<?php

declare(strict_types=1);

namespace App\Order\Application\Response;

final readonly class AdminDashboardResponse
{
    /**
     * @param list<LowStockProductResponse> $lowStock
     */
    public function __construct(
        public int $revenueCents,
        public int $todayOrders,
        public int $todayRevenueCents,
        public array $lowStock,
        public string $currency = 'EUR',
    ) {
    }

    /**
     * @return array{
     *     revenue: array{cents: int, currency: string},
     *     today: array{orders: int, revenue: array{cents: int, currency: string}},
     *     lowStock: list<array{id: string, name: string, slug: string, stock: int}>
     * }
     */
    public function toArray(): array
    {
        return [
            'revenue' => ['cents' => $this->revenueCents, 'currency' => $this->currency],
            'today' => [
                'orders' => $this->todayOrders,
                'revenue' => ['cents' => $this->todayRevenueCents, 'currency' => $this->currency],
            ],
            'lowStock' => array_map(
                static fn (LowStockProductResponse $product): array => $product->toArray(),
                $this->lowStock,
            ),
        ];
    }
}
