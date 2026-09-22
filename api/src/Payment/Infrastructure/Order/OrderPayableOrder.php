<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Order;

use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\OrderId;
use App\Payment\Application\Port\PayableOrder;

final readonly class OrderPayableOrder implements PayableOrder
{
    public function __construct(private OrderRepository $orders)
    {
    }

    public function isPending(string $orderId): bool
    {
        $order = $this->orders->findById(OrderId::fromString($orderId));

        return $order !== null && $order->status()->isPending();
    }
}
