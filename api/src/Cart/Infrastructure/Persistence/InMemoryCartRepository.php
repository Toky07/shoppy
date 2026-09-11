<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CustomerId;

final class InMemoryCartRepository implements CartRepository
{
    /** @var array<string, Cart> */
    private array $byCustomerId = [];

    public function save(Cart $cart): void
    {
        $this->byCustomerId[$cart->customerId()->value()] = $cart;
    }

    public function findByCustomerId(CustomerId $customerId): ?Cart
    {
        return $this->byCustomerId[$customerId->value()] ?? null;
    }
}
