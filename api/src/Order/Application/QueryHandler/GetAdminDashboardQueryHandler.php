<?php

declare(strict_types=1);

namespace App\Order\Application\QueryHandler;

use App\Order\Application\Query\GetAdminDashboardQuery;
use App\Order\Application\Response\AdminDashboardResponse;
use App\Order\Application\Response\LowStockProductResponse;
use App\Order\Domain\Repository\OrderRepository;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Shared\Domain\Clock;

final readonly class GetAdminDashboardQueryHandler
{
    public const LOW_STOCK_THRESHOLD = 5;

    public const LOW_STOCK_LIMIT = 8;

    public function __construct(
        private OrderRepository $orderRepository,
        private ProductRepository $productRepository,
        private Clock $clock,
    ) {
    }

    public function handle(GetAdminDashboardQuery $query): AdminDashboardResponse
    {
        $now = $this->clock->now();
        $startOfDay = $now->setTime(0, 0, 0);
        $startOfTomorrow = $startOfDay->modify('+1 day');

        return new AdminDashboardResponse(
            $this->orderRepository->paidRevenueCents(),
            $this->orderRepository->countPlacedBetween($startOfDay, $startOfTomorrow),
            $this->orderRepository->paidRevenueCents($startOfDay, $startOfTomorrow),
            array_map(
                static fn (Product $product): LowStockProductResponse => new LowStockProductResponse(
                    $product->id()->value(),
                    $product->name()->value(),
                    $product->slug()->value(),
                    $product->stock()->value(),
                ),
                $this->productRepository->findLowStock(self::LOW_STOCK_THRESHOLD, self::LOW_STOCK_LIMIT),
            ),
        );
    }
}
