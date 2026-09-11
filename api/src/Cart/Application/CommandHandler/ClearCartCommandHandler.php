<?php

declare(strict_types=1);

namespace App\Cart\Application\CommandHandler;

use App\Cart\Application\Command\ClearCartCommand;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Shared\Domain\Clock;

final readonly class ClearCartCommandHandler
{
    public function __construct(
        private CartRepository $cartRepository,
        private Clock $clock,
    ) {
    }

    public function handle(ClearCartCommand $command): void
    {
        $customerId = CustomerId::fromString($command->customerId);
        $cart = $this->cartRepository->findByCustomerId($customerId);

        if ($cart === null) {
            return;
        }

        $cart->clear($this->clock->now());
        $this->cartRepository->save($cart);
    }
}
