<?php

declare(strict_types=1);

namespace App\Cart\Domain\ValueObject;

final readonly class CartItem
{
    private function __construct(
        private CartProductId $productId,
        private CartQuantity $quantity,
        private ?CartVariantId $variantId,
    ) {
    }

    public static function of(CartProductId $productId, CartQuantity $quantity, ?CartVariantId $variantId = null): self
    {
        return new self($productId, $quantity, $variantId);
    }

    public function productId(): CartProductId
    {
        return $this->productId;
    }

    public function variantId(): ?CartVariantId
    {
        return $this->variantId;
    }

    public function quantity(): CartQuantity
    {
        return $this->quantity;
    }

    public function matches(CartProductId $productId, ?CartVariantId $variantId): bool
    {
        return $this->productId->equals($productId)
            && $this->variantId?->value() === $variantId?->value();
    }

    public function withQuantity(CartQuantity $quantity): self
    {
        return new self($this->productId, $quantity, $this->variantId);
    }
}
