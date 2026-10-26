<?php

declare(strict_types=1);

namespace App\Cart\Application\CommandHandler;

use App\Cart\Application\AvailableStock;
use App\Cart\Application\Catalog;
use App\Cart\Application\Command\MergeCartCommand;
use App\Cart\Application\Command\MergeCartLine;
use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CartId;
use App\Cart\Domain\ValueObject\CartItem;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartQuantity;
use App\Cart\Domain\ValueObject\CartVariantId;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Shared\Application\Transaction\TransactionRunner;
use App\Shared\Domain\Clock;

final readonly class MergeCartCommandHandler
{
    public function __construct(
        private Catalog $catalog,
        private AvailableStock $availableStock,
        private CartRepository $cartRepository,
        private TransactionRunner $transactions,
        private Clock $clock,
    ) {
    }

    public function handle(MergeCartCommand $command): void
    {
        $this->transactions->run(function () use ($command): void {
            $lines = $this->lines($command->items);

            if ($lines === []) {
                return;
            }

            $customerId = CustomerId::fromString($command->customerId);
            $snapshots = $this->catalog->findByItems($lines);
            $reserved = $this->cartRepository->reservedQuantities($lines, $customerId);
            $cart = $this->cartRepository->findByCustomerId($customerId);
            $now = $this->clock->now();
            $cart ??= Cart::create(CartId::generate(), $customerId, $now);
            $mutated = false;

            foreach ($lines as $line) {
                $snapshot = $snapshots[$line->lineKey()] ?? null;

                if ($snapshot === null || ($snapshot->requiresVariant && $line->variantId() === null)) {
                    continue;
                }

                $own = $this->ownQuantity($cart, $line);
                $room = max(0, $this->availableStock->remaining($snapshot->stock, $reserved[$line->lineKey()] ?? 0) - $own);
                $take = min($line->quantity()->value(), $room);

                if ($take < 1) {
                    continue;
                }

                $cart->addItem($line->productId(), CartQuantity::fromInt($take), $now, $line->variantId());
                $mutated = true;
            }

            if ($mutated) {
                $this->cartRepository->save($cart);
            }
        });
    }

    /**
     * @param list<MergeCartLine> $items
     *
     * @return list<CartItem>
     */
    private function lines(array $items): array
    {
        $lines = [];

        foreach ($items as $item) {
            if ($item->quantity < 1) {
                continue;
            }

            $lines[] = CartItem::of(
                CartProductId::fromString($item->productId),
                CartQuantity::fromInt($item->quantity),
                $item->variantId === null ? null : CartVariantId::fromString($item->variantId),
            );
        }

        return $lines;
    }

    private function ownQuantity(Cart $cart, CartItem $line): int
    {
        foreach ($cart->items() as $item) {
            if ($item->matches($line->productId(), $line->variantId())) {
                return $item->quantity()->value();
            }
        }

        return 0;
    }
}
