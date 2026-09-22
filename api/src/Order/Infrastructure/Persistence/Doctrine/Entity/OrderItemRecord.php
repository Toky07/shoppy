<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Persistence\Doctrine\Entity;

use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_items')]
class OrderItemRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrderRecord::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', nullable: false, onDelete: 'CASCADE')]
    private OrderRecord $order;

    #[ORM\Column(name: 'product_id', length: 36)]
    private string $productId;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(name: 'unit_price_cents')]
    private int $unitPriceCents;

    #[ORM\Column(length: 3)]
    private string $currency;

    #[ORM\Column]
    private int $quantity;

    #[ORM\Column(name: 'variant_id', length: 36, nullable: true)]
    private ?string $variantId = null;

    #[ORM\Column]
    private int $position;

    public static function fromDomain(OrderRecord $order, OrderItem $item, int $position): self
    {
        $record = new self();
        $record->order = $order;
        $record->productId = $item->catalogProductId()->value();
        $record->name = $item->name()->value();
        $record->unitPriceCents = $item->unitPrice()->cents();
        $record->currency = $item->unitPrice()->currency();
        $record->quantity = $item->quantity()->value();
        $record->variantId = $item->variantId();
        $record->position = $position;

        return $record;
    }

    public function toDomain(): OrderItem
    {
        return OrderItem::of(
            CatalogProductId::fromString($this->productId),
            OrderedProductName::fromString($this->name),
            UnitPrice::fromCents($this->unitPriceCents),
            Quantity::fromInt($this->quantity),
            $this->variantId,
        );
    }
}
