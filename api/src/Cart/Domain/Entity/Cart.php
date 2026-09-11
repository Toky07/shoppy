<?php

declare(strict_types=1);

namespace App\Cart\Domain\Entity;

use App\Cart\Domain\Exception\CartItemNotFound;
use App\Cart\Domain\Exception\EmptyCart;
use App\Cart\Domain\ValueObject\CartId;
use App\Cart\Domain\ValueObject\CartItem;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartQuantity;
use App\Cart\Domain\ValueObject\CustomerId;
use DateTimeImmutable;

final class Cart
{
    /**
     * @param list<CartItem> $items
     */
    private function __construct(
        private CartId $id,
        private CustomerId $customerId,
        private array $items,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(CartId $id, CustomerId $customerId, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $customerId, [], $updatedAt);
    }

    /**
     * @param list<CartItem> $items
     */
    public static function reconstitute(
        CartId $id,
        CustomerId $customerId,
        array $items,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $customerId, $items, $updatedAt);
    }

    public function id(): CartId
    {
        return $this->id;
    }

    public function customerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return list<CartItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function addItem(CartProductId $productId, CartQuantity $quantity, DateTimeImmutable $updatedAt): void
    {
        foreach ($this->items as $index => $item) {
            if ($item->productId()->equals($productId)) {
                $this->items[$index] = $item->withQuantity($item->quantity()->add($quantity));
                $this->updatedAt = $updatedAt;

                return;
            }
        }

        $this->items[] = CartItem::of($productId, $quantity);
        $this->updatedAt = $updatedAt;
    }

    public function setItemQuantity(CartProductId $productId, CartQuantity $quantity, DateTimeImmutable $updatedAt): void
    {
        foreach ($this->items as $index => $item) {
            if ($item->productId()->equals($productId)) {
                $this->items[$index] = $item->withQuantity($quantity);
                $this->updatedAt = $updatedAt;

                return;
            }
        }

        throw new CartItemNotFound($productId);
    }

    public function removeItem(CartProductId $productId, DateTimeImmutable $updatedAt): void
    {
        foreach ($this->items as $index => $item) {
            if ($item->productId()->equals($productId)) {
                unset($this->items[$index]);
                $this->items = array_values($this->items);
                $this->updatedAt = $updatedAt;

                return;
            }
        }

        throw new CartItemNotFound($productId);
    }

    public function clear(DateTimeImmutable $updatedAt): void
    {
        $this->items = [];
        $this->updatedAt = $updatedAt;
    }

    public function assertNotEmpty(): void
    {
        if ($this->isEmpty()) {
            throw new EmptyCart();
        }
    }
}
