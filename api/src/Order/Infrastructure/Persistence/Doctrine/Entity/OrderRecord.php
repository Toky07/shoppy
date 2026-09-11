<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Persistence\Doctrine\Entity;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderStatus;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
#[ORM\Index(name: 'idx_orders_customer_created_at', columns: ['customer_id', 'created_at'])]
class OrderRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(name: 'customer_id', length: 36)]
    private string $customerId;

    #[ORM\Column(length: 32)]
    private string $status;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    /** @var Collection<int, OrderItemRecord> */
    #[ORM\OneToMany(targetEntity: OrderItemRecord::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public static function fromDomain(Order $order): self
    {
        $record = new self();
        $record->id = $order->id()->value();
        $record->customerId = $order->customerId()->value();
        $record->status = $order->status()->value();
        $record->createdAt = $order->createdAt();

        foreach ($order->items() as $position => $item) {
            $record->items->add(OrderItemRecord::fromDomain($record, $item, $position));
        }

        return $record;
    }

    public function updateFromDomain(Order $order): void
    {
        $this->status = $order->status()->value();
    }

    public function toDomain(): Order
    {
        return Order::reconstitute(
            OrderId::fromString($this->id),
            CustomerId::fromString($this->customerId),
            array_values($this->items->map(
                static fn (OrderItemRecord $item): OrderItem => $item->toDomain(),
            )->toArray()),
            OrderStatus::fromString($this->status),
            $this->createdAt,
        );
    }
}
