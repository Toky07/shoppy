<?php

declare(strict_types=1);

namespace App\Cart\Application;

use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartVariantId;
use App\Cart\Domain\ValueObject\CustomerId;

final readonly class AvailableStock
{
    public function __construct(
        private CartRepository $cartRepository,
        private Catalog $catalog,
    ) {
    }

    /**
     * Maximum quantity this customer may hold, or null when the product is unknown.
     * Other carts' reservations are already subtracted.
     */
    public function maximum(
        CustomerId $customerId,
        CartProductId $productId,
        ?CartVariantId $variantId,
    ): ?int {
        $snapshot = $this->catalog->findById($productId, $variantId?->value());

        if ($snapshot === null) {
            return null;
        }

        return max(0, $snapshot->stock - $this->cartRepository->reservedQuantity(
            $productId,
            $variantId,
            $customerId,
        ));
    }
}
