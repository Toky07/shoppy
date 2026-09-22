<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Exception\CartAlreadyCheckingOut;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartVariantId;
use App\Cart\Domain\ValueObject\CustomerId;

final class InMemoryCartRepository implements CartRepository
{
    /** @var array<string, Cart> */
    private array $byCustomerId = [];

    /** @var array<string, int> */
    private array $versions = [];

    public function save(Cart $cart): void
    {
        $key = $cart->customerId()->value();
        $this->byCustomerId[$key] = $cart;
        $this->versions[$key] = $cart->version();
    }

    public function findByCustomerId(CustomerId $customerId): ?Cart
    {
        $cart = $this->byCustomerId[$customerId->value()] ?? null;

        return $cart === null ? null : clone $cart;
    }

    public function claimForCheckout(Cart $cart): void
    {
        $key = $cart->customerId()->value();

        if (($this->versions[$key] ?? null) !== $cart->version()) {
            throw new CartAlreadyCheckingOut();
        }

        $cart->claimCheckout();
        $this->versions[$key] = $cart->version();
    }

    public function reservedQuantity(
        CartProductId $productId,
        ?CartVariantId $variantId,
        CustomerId $exceptCustomerId,
    ): int {
        $total = 0;

        foreach ($this->byCustomerId as $customerId => $cart) {
            if ($customerId === $exceptCustomerId->value()) {
                continue;
            }

            foreach ($cart->items() as $item) {
                if ($item->matches($productId, $variantId)) {
                    $total += $item->quantity()->value();
                }
            }
        }

        return $total;
    }
}
