<?php

declare(strict_types=1);

namespace App\Cart\Application\Response;

use App\Cart\Domain\Entity\Cart;
use DateTimeInterface;

final readonly class CartResponse
{
    /**
     * @param list<CartItemResponse> $items
     */
    public function __construct(
        public ?string $id,
        public string $customerId,
        public array $items,
        public int $totalCents,
        public ?string $updatedAt,
    ) {
    }

    /**
     * @param list<CartItemResponse> $items
     */
    public static function fromCart(Cart $cart, array $items): self
    {
        $totalCents = array_reduce(
            $items,
            static fn (int $total, CartItemResponse $item): int => $total + $item->lineTotalCents,
            0,
        );

        return new self(
            $cart->id()->value(),
            $cart->customerId()->value(),
            $items,
            $totalCents,
            $cart->updatedAt()->format(DateTimeInterface::ATOM),
        );
    }

    public static function empty(string $customerId): self
    {
        return new self(null, $customerId, [], 0, null);
    }

    /**
     * @return array{
     *     id: string|null,
     *     customerId: string,
     *     items: list<array<string, mixed>>,
     *     total: array{cents: int, currency: string},
     *     updatedAt: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customerId,
            'items' => array_map(
                static fn (CartItemResponse $item): array => $item->toArray(),
                $this->items,
            ),
            'total' => [
                'cents' => $this->totalCents,
                'currency' => 'EUR',
            ],
            'updatedAt' => $this->updatedAt,
        ];
    }
}
