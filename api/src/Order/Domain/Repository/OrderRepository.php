<?php

declare(strict_types=1);

namespace App\Order\Domain\Repository;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;

interface OrderRepository
{
    public function save(Order $order): void;

    public function findById(OrderId $id): ?Order;

    /**
     * @return list<Order>
     */
    public function findPageByCustomer(CustomerId $customerId, int $offset, int $limit): array;

    public function countByCustomer(CustomerId $customerId): int;

    /**
     * @return list<Order>
     */
    public function findPage(int $offset, int $limit): array;

    public function countAll(): int;
}
