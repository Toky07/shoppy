<?php

declare(strict_types=1);

namespace App\Order\Domain\ValueObject;

final readonly class OrderItem
{
    private function __construct(
        private CatalogProductId $catalogProductId,
        private OrderedProductName $name,
        private UnitPrice $unitPrice,
        private Quantity $quantity,
        private ?string $variantId,
    ) {
    }

    public static function of(
        CatalogProductId $catalogProductId,
        OrderedProductName $name,
        UnitPrice $unitPrice,
        Quantity $quantity,
        ?string $variantId = null,
    ): self {
        return new self($catalogProductId, $name, $unitPrice, $quantity, $variantId);
    }

    public function catalogProductId(): CatalogProductId
    {
        return $this->catalogProductId;
    }

    public function name(): OrderedProductName
    {
        return $this->name;
    }

    public function unitPrice(): UnitPrice
    {
        return $this->unitPrice;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function variantId(): ?string
    {
        return $this->variantId;
    }

    public function lineTotalCents(): int
    {
        return $this->unitPrice->cents() * $this->quantity->value();
    }
}
