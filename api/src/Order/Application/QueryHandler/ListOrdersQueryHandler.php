<?php

declare(strict_types=1);

namespace App\Order\Application\QueryHandler;

use App\Order\Application\Query\ListOrdersQuery;
use App\Order\Application\Response\OrderListResponse;
use App\Order\Application\Response\OrderResponse;
use App\Order\Domain\Exception\InvalidOrderPagination;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\CustomerId;

final readonly class ListOrdersQueryHandler
{
    public function __construct(private OrderRepository $orderRepository)
    {
    }

    public function handle(ListOrdersQuery $query): OrderListResponse
    {
        if ($query->page < 1 || $query->limit < 1 || $query->limit > ListOrdersQuery::MAX_LIMIT) {
            throw new InvalidOrderPagination();
        }

        $customerId = CustomerId::fromString($query->customerId);
        $offset = ($query->page - 1) * $query->limit;
        $orders = $this->orderRepository->findPageByCustomer($customerId, $offset, $query->limit);

        return new OrderListResponse(
            array_map(OrderResponse::fromOrder(...), $orders),
            $query->page,
            $query->limit,
            $this->orderRepository->countByCustomer($customerId),
        );
    }
}
