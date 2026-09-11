<?php

declare(strict_types=1);

namespace App\Cart\Domain\ValueObject;

final readonly class CartItem
{
    private function __construct(
        private CartProductId $productId,
        private CartQuantity $quantity,
    ) {
    }

    public static function of(CartProductId $productId, CartQuantity $quantity): self
    {
        return new self($productId, $quantity);
    }

    public function productId(): CartProductId
    {
        return $this->productId;
    }

    public function quantity(): CartQuantity
    {
        return $this->quantity;
    }

    public function withQuantity(CartQuantity $quantity): self
    {
        return new self($this->productId, $quantity);
    }
}
