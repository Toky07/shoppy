<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartVariantId;
use App\Cart\Domain\ValueObject\CustomerId;

final class CountingCartRepository implements CartRepository
{
    public int $saveCalls = 0;

    public int $findByCustomerIdCalls = 0;

    public int $reservedQuantityCalls = 0;

    public int $reservedQuantitiesCalls = 0;

    public function __construct(private CartRepository $inner)
    {
    }

    public function save(Cart $cart): void
    {
        $this->saveCalls++;
        $this->inner->save($cart);
    }

    public function findByCustomerId(CustomerId $customerId): ?Cart
    {
        $this->findByCustomerIdCalls++;

        return $this->inner->findByCustomerId($customerId);
    }

    public function claimForCheckout(Cart $cart): void
    {
        $this->inner->claimForCheckout($cart);
    }

    public function reservedQuantity(
        CartProductId $productId,
        ?CartVariantId $variantId,
        CustomerId $exceptCustomerId,
    ): int {
        $this->reservedQuantityCalls++;

        return $this->inner->reservedQuantity($productId, $variantId, $exceptCustomerId);
    }

    public function reservedQuantities(array $items, CustomerId $exceptCustomerId): array
    {
        $this->reservedQuantitiesCalls++;

        return $this->inner->reservedQuantities($items, $exceptCustomerId);
    }
}
