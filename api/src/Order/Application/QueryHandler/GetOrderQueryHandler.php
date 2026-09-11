<?php

declare(strict_types=1);

namespace App\Order\Application\QueryHandler;

use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\Response\OrderResponse;
use App\Order\Domain\Exception\OrderNotFound;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\OrderId;

final readonly class GetOrderQueryHandler
{
    public function __construct(private OrderRepository $orderRepository)
    {
    }

    public function handle(GetOrderQuery $query): OrderResponse
    {
        $id = OrderId::fromString($query->id);
        $order = $this->orderRepository->findById($id);

        if ($order === null) {
            throw new OrderNotFound($id);
        }

        return OrderResponse::fromOrder($order);
    }
}
