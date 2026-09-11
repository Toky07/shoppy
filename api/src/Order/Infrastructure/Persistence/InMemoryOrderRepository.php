<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Persistence;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;

final class InMemoryOrderRepository implements OrderRepository
{
    /** @var array<string, Order> */
    private array $orders = [];

    public function save(Order $order): void
    {
        $this->orders[$order->id()->value()] = $order;
    }

    public function findById(OrderId $id): ?Order
    {
        return $this->orders[$id->value()] ?? null;
    }

    public function findPageByCustomer(CustomerId $customerId, int $offset, int $limit): array
    {
        $orders = array_values(array_filter(
            $this->orders,
            static fn (Order $order): bool => $order->customerId()->value() === $customerId->value(),
        ));

        usort(
            $orders,
            static fn (Order $left, Order $right): int => $right->createdAt() <=> $left->createdAt(),
        );

        return array_values(array_slice($orders, $offset, $limit));
    }

    public function countByCustomer(CustomerId $customerId): int
    {
        return count(array_filter(
            $this->orders,
            static fn (Order $order): bool => $order->customerId()->value() === $customerId->value(),
        ));
    }

    public function findPage(int $offset, int $limit): array
    {
        $orders = array_values($this->orders);

        usort(
            $orders,
            static fn (Order $left, Order $right): int => [$right->createdAt(), $right->id()->value()]
                <=> [$left->createdAt(), $left->id()->value()],
        );

        return array_values(array_slice($orders, $offset, $limit));
    }

    public function countAll(): int
    {
        return count($this->orders);
    }

    /**
     * @return list<Order>
     */
    public function all(): array
    {
        return array_values($this->orders);
    }
}
