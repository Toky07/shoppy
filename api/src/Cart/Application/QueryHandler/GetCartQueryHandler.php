<?php

declare(strict_types=1);

namespace App\Cart\Application\QueryHandler;

use App\Cart\Application\AvailableStock;
use App\Cart\Application\Catalog;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\Response\CartItemResponse;
use App\Cart\Application\Response\CartResponse;
use App\Cart\Domain\Exception\CartProductNotFound;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CustomerId;

final readonly class GetCartQueryHandler
{
    public function __construct(
        private CartRepository $cartRepository,
        private Catalog $catalog,
        private AvailableStock $availableStock,
    ) {
    }

    public function handle(GetCartQuery $query): CartResponse
    {
        $customerId = CustomerId::fromString($query->customerId);
        $cart = $this->cartRepository->findByCustomerId($customerId);

        if ($cart === null) {
            return CartResponse::empty($customerId->value());
        }

        $lines = $cart->items();
        $snapshots = $this->catalog->findByItems($lines);
        $reserved = $this->cartRepository->reservedQuantities($lines, $customerId);

        $items = [];
        foreach ($lines as $item) {
            $snapshot = $snapshots[$item->lineKey()] ?? null;

            if ($snapshot === null) {
                throw new CartProductNotFound($item->productId());
            }

            $quantity = $item->quantity()->value();
            $items[] = new CartItemResponse(
                $snapshot->id,
                $snapshot->name,
                $quantity,
                $snapshot->unitPriceCents,
                $snapshot->unitPriceCents * $quantity,
                $this->availableStock->remaining($snapshot->stock, $reserved[$item->lineKey()] ?? 0),
                $item->variantId()?->value(),
                $snapshot->imageUrl,
            );
        }

        return CartResponse::fromCart($cart, $items);
    }
}
