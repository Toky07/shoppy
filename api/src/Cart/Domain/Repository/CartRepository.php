<?php

declare(strict_types=1);

namespace App\Cart\Domain\Repository;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartVariantId;
use App\Cart\Domain\ValueObject\CustomerId;

interface CartRepository
{
    public function save(Cart $cart): void;

    public function findByCustomerId(CustomerId $customerId): ?Cart;

    public function claimForCheckout(Cart $cart): void;

    public function reservedQuantity(
        CartProductId $productId,
        ?CartVariantId $variantId,
        CustomerId $exceptCustomerId,
    ): int;
}
