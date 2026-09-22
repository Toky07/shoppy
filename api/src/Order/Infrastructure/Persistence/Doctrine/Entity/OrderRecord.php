<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Persistence\Doctrine\Entity;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderStatus;
use App\Order\Domain\ValueObject\PostalAddress;
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

    #[ORM\Column(name: 'shipping_recipient', length: 80, nullable: true)]
    private ?string $shippingRecipient = null;

    #[ORM\Column(name: 'shipping_line1', length: 120, nullable: true)]
    private ?string $shippingLine1 = null;

    #[ORM\Column(name: 'shipping_line2', length: 120, nullable: true)]
    private ?string $shippingLine2 = null;

    #[ORM\Column(name: 'shipping_postal_code', length: 12, nullable: true)]
    private ?string $shippingPostalCode = null;

    #[ORM\Column(name: 'shipping_city', length: 80, nullable: true)]
    private ?string $shippingCity = null;

    #[ORM\Column(name: 'shipping_country', length: 2, nullable: true)]
    private ?string $shippingCountry = null;

    #[ORM\Column(name: 'billing_recipient', length: 80, nullable: true)]
    private ?string $billingRecipient = null;

    #[ORM\Column(name: 'billing_line1', length: 120, nullable: true)]
    private ?string $billingLine1 = null;

    #[ORM\Column(name: 'billing_line2', length: 120, nullable: true)]
    private ?string $billingLine2 = null;

    #[ORM\Column(name: 'billing_postal_code', length: 12, nullable: true)]
    private ?string $billingPostalCode = null;

    #[ORM\Column(name: 'billing_city', length: 80, nullable: true)]
    private ?string $billingCity = null;

    #[ORM\Column(name: 'billing_country', length: 2, nullable: true)]
    private ?string $billingCountry = null;

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
        $record->writeAddress('shipping', $order->shippingAddress());
        $record->writeAddress('billing', $order->billingAddress());

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
            $this->readAddress('shipping'),
            $this->readAddress('billing'),
        );
    }

    private function writeAddress(string $prefix, ?PostalAddress $address): void
    {
        $recipient = $prefix.'Recipient';
        $line1 = $prefix.'Line1';
        $line2 = $prefix.'Line2';
        $postalCode = $prefix.'PostalCode';
        $city = $prefix.'City';
        $country = $prefix.'Country';

        $this->{$recipient} = $address?->recipient();
        $this->{$line1} = $address?->line1();
        $this->{$line2} = $address?->line2();
        $this->{$postalCode} = $address?->postalCode();
        $this->{$city} = $address?->city();
        $this->{$country} = $address?->country();
    }

    private function readAddress(string $prefix): ?PostalAddress
    {
        $recipient = $this->{$prefix.'Recipient'};
        $line1 = $this->{$prefix.'Line1'};
        $postalCode = $this->{$prefix.'PostalCode'};
        $city = $this->{$prefix.'City'};
        $country = $this->{$prefix.'Country'};

        if ($recipient === null || $line1 === null || $postalCode === null || $city === null || $country === null) {
            return null;
        }

        return PostalAddress::fromInput(
            $recipient,
            $line1,
            $this->{$prefix.'Line2'},
            $postalCode,
            $city,
            $country,
            $prefix,
        );
    }
}
