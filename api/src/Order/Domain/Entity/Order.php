<?php

declare(strict_types=1);

namespace App\Order\Domain\Entity;

use App\Order\Domain\Exception\EmptyOrder;
use App\Order\Domain\Exception\InvalidOrderTransition;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderStatus;
use App\Order\Domain\ValueObject\PostalAddress;
use DateTimeImmutable;

final class Order
{
    /**
     * @param list<OrderItem> $items
     */
    private function __construct(
        private OrderId $id,
        private CustomerId $customerId,
        private array $items,
        private OrderStatus $status,
        private DateTimeImmutable $createdAt,
        private ?PostalAddress $shippingAddress,
        private ?PostalAddress $billingAddress,
    ) {
    }

    /**
     * @param list<OrderItem> $items
     */
    public static function place(
        OrderId $id,
        CustomerId $customerId,
        array $items,
        DateTimeImmutable $createdAt,
        PostalAddress $shippingAddress,
        PostalAddress $billingAddress,
    ): self {
        if ($items === []) {
            throw new EmptyOrder();
        }

        return new self($id, $customerId, $items, OrderStatus::pending(), $createdAt, $shippingAddress, $billingAddress);
    }

    /**
     * @param list<OrderItem> $items
     */
    public static function reconstitute(
        OrderId $id,
        CustomerId $customerId,
        array $items,
        OrderStatus $status,
        DateTimeImmutable $createdAt,
        ?PostalAddress $shippingAddress = null,
        ?PostalAddress $billingAddress = null,
    ): self {
        if ($items === []) {
            throw new EmptyOrder();
        }

        return new self($id, $customerId, $items, $status, $createdAt, $shippingAddress, $billingAddress);
    }

    public function id(): OrderId
    {
        return $this->id;
    }

    public function customerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return list<OrderItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function shippingAddress(): ?PostalAddress
    {
        return $this->shippingAddress;
    }

    public function billingAddress(): ?PostalAddress
    {
        return $this->billingAddress;
    }

    public function totalCents(): int
    {
        return array_reduce(
            $this->items,
            static fn (int $total, OrderItem $item): int => $total + $item->lineTotalCents(),
            0,
        );
    }

    public function cancel(): void
    {
        if (!$this->status->isPending()) {
            throw new InvalidOrderTransition($this->status, OrderStatus::cancelled()->value());
        }

        $this->status = OrderStatus::cancelled();
    }

    public function markPaid(): void
    {
        if (!$this->status->isPending()) {
            throw new InvalidOrderTransition($this->status, OrderStatus::paid()->value());
        }

        $this->status = OrderStatus::paid();
    }
}
