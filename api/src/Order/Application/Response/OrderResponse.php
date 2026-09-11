<?php

declare(strict_types=1);

namespace App\Order\Application\Response;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\OrderItem;
use DateTimeInterface;

final readonly class OrderResponse
{
    /**
     * @param list<array{
     *     productId: string,
     *     name: string,
     *     quantity: int,
     *     unitPrice: array{cents: int, currency: string},
     *     lineTotal: array{cents: int, currency: string}
     * }> $items
     * @param array{cents: int, currency: string} $total
     */
    public function __construct(
        public string $id,
        public string $customerId,
        public string $status,
        public array $items,
        public array $total,
        public string $createdAt,
    ) {
    }

    public static function fromOrder(Order $order): self
    {
        $items = array_map(
            static fn (OrderItem $item): array => [
                'productId' => $item->catalogProductId()->value(),
                'name' => $item->name()->value(),
                'quantity' => $item->quantity()->value(),
                'unitPrice' => [
                    'cents' => $item->unitPrice()->cents(),
                    'currency' => $item->unitPrice()->currency(),
                ],
                'lineTotal' => [
                    'cents' => $item->lineTotalCents(),
                    'currency' => $item->unitPrice()->currency(),
                ],
            ],
            $order->items(),
        );

        return new self(
            $order->id()->value(),
            $order->customerId()->value(),
            $order->status()->value(),
            $items,
            [
                'cents' => $order->totalCents(),
                'currency' => $order->items()[0]->unitPrice()->currency(),
            ],
            $order->createdAt()->format(DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     customerId: string,
     *     status: string,
     *     items: list<array{
     *         productId: string,
     *         name: string,
     *         quantity: int,
     *         unitPrice: array{cents: int, currency: string},
     *         lineTotal: array{cents: int, currency: string}
     *     }>,
     *     total: array{cents: int, currency: string},
     *     createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customerId,
            'status' => $this->status,
            'items' => $this->items,
            'total' => $this->total,
            'createdAt' => $this->createdAt,
        ];
    }
}
