<?php

declare(strict_types=1);

namespace App\Cart\Application\CommandHandler;

use App\Cart\Application\Catalog;
use App\Cart\Application\Command\AddToCartCommand;
use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Exception\CartProductNotFound;
use App\Cart\Domain\Exception\InsufficientCartStock;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CartId;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartQuantity;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Shared\Domain\Clock;

final readonly class AddToCartCommandHandler
{
    public function __construct(
        private CartRepository $cartRepository,
        private Catalog $catalog,
        private Clock $clock,
    ) {
    }

    public function handle(AddToCartCommand $command): void
    {
        $customerId = CustomerId::fromString($command->customerId);
        $productId = CartProductId::fromString($command->productId);
        $quantity = CartQuantity::fromInt($command->quantity);
        $snapshot = $this->catalog->findById($productId);

        if ($snapshot === null) {
            throw new CartProductNotFound($productId);
        }

        $cart = $this->cartRepository->findByCustomerId($customerId)
            ?? Cart::create(CartId::generate(), $customerId, $this->clock->now());

        $existingQuantity = 0;
        foreach ($cart->items() as $item) {
            if ($item->productId()->equals($productId)) {
                $existingQuantity = $item->quantity()->value();
                break;
            }
        }

        $requestedTotal = $existingQuantity + $quantity->value();
        if ($snapshot->stock < $requestedTotal) {
            throw new InsufficientCartStock($snapshot->stock, $requestedTotal);
        }

        $cart->addItem($productId, $quantity, $this->clock->now());
        $this->cartRepository->save($cart);
    }
}
