<?php

declare(strict_types=1);

namespace App\Cart\Application\CommandHandler;

use App\Cart\Application\Catalog;
use App\Cart\Application\Command\UpdateCartItemQuantityCommand;
use App\Cart\Domain\Exception\CartItemNotFound;
use App\Cart\Domain\Exception\CartProductNotFound;
use App\Cart\Domain\Exception\InsufficientCartStock;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartQuantity;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Shared\Domain\Clock;

final readonly class UpdateCartItemQuantityCommandHandler
{
    public function __construct(
        private CartRepository $cartRepository,
        private Catalog $catalog,
        private Clock $clock,
    ) {
    }

    public function handle(UpdateCartItemQuantityCommand $command): void
    {
        $customerId = CustomerId::fromString($command->customerId);
        $productId = CartProductId::fromString($command->productId);
        $quantity = CartQuantity::fromInt($command->quantity);
        $cart = $this->cartRepository->findByCustomerId($customerId);

        if ($cart === null) {
            throw new CartItemNotFound($productId);
        }

        $snapshot = $this->catalog->findById($productId);
        if ($snapshot === null) {
            throw new CartProductNotFound($productId);
        }

        if ($snapshot->stock < $quantity->value()) {
            throw new InsufficientCartStock($snapshot->stock, $quantity->value());
        }

        $cart->setItemQuantity($productId, $quantity, $this->clock->now());
        $this->cartRepository->save($cart);
    }
}
