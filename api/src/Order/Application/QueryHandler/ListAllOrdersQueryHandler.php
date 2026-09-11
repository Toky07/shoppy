<?php

declare(strict_types=1);

namespace App\Order\Application\QueryHandler;

use App\Order\Application\Query\ListAllOrdersQuery;
use App\Order\Application\Response\OrderListResponse;
use App\Order\Application\Response\OrderResponse;
use App\Order\Domain\Exception\InvalidOrderPagination;
use App\Order\Domain\Repository\OrderRepository;

final readonly class ListAllOrdersQueryHandler
{
    public function __construct(private OrderRepository $orderRepository)
    {
    }

    public function handle(ListAllOrdersQuery $query): OrderListResponse
    {
        if ($query->page < 1 || $query->limit < 1 || $query->limit > ListAllOrdersQuery::MAX_LIMIT) {
            throw new InvalidOrderPagination();
        }

        $offset = ($query->page - 1) * $query->limit;
        $orders = $this->orderRepository->findPage($offset, $query->limit);

        return new OrderListResponse(
            array_map(OrderResponse::fromOrder(...), $orders),
            $query->page,
            $query->limit,
            $this->orderRepository->countAll(),
        );
    }
}
