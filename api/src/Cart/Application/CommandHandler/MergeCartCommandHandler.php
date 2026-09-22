<?php

declare(strict_types=1);

namespace App\Cart\Application\CommandHandler;

use App\Cart\Application\AvailableStock;
use App\Cart\Application\Command\AddToCartCommand;
use App\Cart\Application\Command\MergeCartCommand;
use App\Cart\Domain\Exception\CartProductNotFound;
use App\Cart\Domain\Exception\CartVariantRequired;
use App\Cart\Domain\Exception\InsufficientCartStock;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartVariantId;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Shared\Application\Transaction\TransactionRunner;

final readonly class MergeCartCommandHandler
{
    public function __construct(
        private AddToCartCommandHandler $addToCart,
        private AvailableStock $availableStock,
        private CartRepository $cartRepository,
        private TransactionRunner $transactions,
    ) {
    }

    public function handle(MergeCartCommand $command): void
    {
        $this->transactions->run(function () use ($command): void {
            $customerId = CustomerId::fromString($command->customerId);

            foreach ($command->items as $line) {
                $productId = CartProductId::fromString($line->productId);
                $variantId = $line->variantId === null ? null : CartVariantId::fromString($line->variantId);
                $maximum = $this->availableStock->maximum($customerId, $productId, $variantId);

                if ($maximum === null || $line->quantity < 1) {
                    continue;
                }

                $own = $this->ownQuantity($customerId, $productId, $variantId);
                $room = max(0, $maximum - $own);
                $take = min($line->quantity, $room);

                if ($take < 1) {
                    continue;
                }

                try {
                    $this->addToCart->handle(new AddToCartCommand(
                        customerId: $command->customerId,
                        productId: $line->productId,
                        quantity: $take,
                        variantId: $line->variantId,
                    ));
                } catch (CartProductNotFound|CartVariantRequired|InsufficientCartStock) {
                    continue;
                }
            }
        });
    }

    private function ownQuantity(CustomerId $customerId, CartProductId $productId, ?CartVariantId $variantId): int
    {
        $cart = $this->cartRepository->findByCustomerId($customerId);

        if ($cart === null) {
            return 0;
        }

        foreach ($cart->items() as $item) {
            if ($item->matches($productId, $variantId)) {
                return $item->quantity()->value();
            }
        }

        return 0;
    }
}
