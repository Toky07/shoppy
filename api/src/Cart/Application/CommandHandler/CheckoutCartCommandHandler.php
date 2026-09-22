<?php

declare(strict_types=1);

namespace App\Cart\Application\CommandHandler;

use App\Cart\Application\Command\CheckoutCartCommand;
use App\Cart\Domain\Exception\EmptyCart;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Order\Application\Command\PlaceOrderCommand;
use App\Order\Application\Command\PlaceOrderLine;
use App\Order\Application\CommandHandler\PlaceOrderCommandHandler;
use App\Order\Domain\ValueObject\OrderId;
use App\Shared\Domain\Clock;

final readonly class CheckoutCartCommandHandler
{
    public function __construct(
        private CartRepository $cartRepository,
        private PlaceOrderCommandHandler $placeOrder,
        private Clock $clock,
    ) {
    }

    public function handle(CheckoutCartCommand $command): OrderId
    {
        $customerId = CustomerId::fromString($command->customerId);
        $cart = $this->cartRepository->findByCustomerId($customerId);

        if ($cart === null || $cart->isEmpty()) {
            throw new EmptyCart();
        }

        $orderId = $this->placeOrder->handle(new PlaceOrderCommand(
            customerId: $customerId->value(),
            items: array_map(
                static fn ($item): PlaceOrderLine => new PlaceOrderLine(
                    $item->productId()->value(),
                    $item->quantity()->value(),
                    $item->variantId()?->value(),
                ),
                $cart->items(),
            ),
        ));

        $cart->clear($this->clock->now());
        $this->cartRepository->save($cart);

        return $orderId;
    }
}
