<?php

declare(strict_types=1);

namespace App\Cart\Application\QueryHandler;

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
    ) {
    }

    public function handle(GetCartQuery $query): CartResponse
    {
        $customerId = CustomerId::fromString($query->customerId);
        $cart = $this->cartRepository->findByCustomerId($customerId);

        if ($cart === null) {
            return CartResponse::empty($customerId->value());
        }

        $items = [];
        foreach ($cart->items() as $item) {
            $snapshot = $this->catalog->findById($item->productId(), $item->variantId()?->value());

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
                $snapshot->stock,
                $item->variantId()?->value(),
            );
        }

        return CartResponse::fromCart($cart, $items);
    }
}
