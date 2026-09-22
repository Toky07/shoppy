<?php

declare(strict_types=1);

namespace App\Cart\Application\CommandHandler;

use App\Cart\Application\Command\RemoveFromCartCommand;
use App\Cart\Domain\Exception\CartItemNotFound;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartVariantId;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Shared\Domain\Clock;

final readonly class RemoveFromCartCommandHandler
{
    public function __construct(
        private CartRepository $cartRepository,
        private Clock $clock,
    ) {
    }

    public function handle(RemoveFromCartCommand $command): void
    {
        $customerId = CustomerId::fromString($command->customerId);
        $productId = CartProductId::fromString($command->productId);
        $variantId = $command->variantId === null ? null : CartVariantId::fromString($command->variantId);
        $cart = $this->cartRepository->findByCustomerId($customerId);

        if ($cart === null) {
            throw new CartItemNotFound($productId);
        }

        $cart->removeItem($productId, $this->clock->now(), $variantId);
        $this->cartRepository->save($cart);
    }
}
