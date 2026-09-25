<?php

declare(strict_types=1);

namespace App\Order\Application\Response;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\PostalAddress;
use App\Order\Domain\ValueObject\ShippingMethod;
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
     * @param array{recipient: string, line1: string, line2: string|null, postalCode: string, city: string, country: string}|null $shippingAddress
     * @param array{recipient: string, line1: string, line2: string|null, postalCode: string, city: string, country: string}|null $billingAddress
     * @param array{method: string, label: string, fee: array{cents: int, currency: string}}|null $shipping
     */
    public function __construct(
        public string $id,
        public string $customerId,
        public string $customerEmail,
        public string $status,
        public array $items,
        public array $total,
        public string $createdAt,
        public ?array $shippingAddress,
        public ?array $billingAddress,
        public ?array $shipping,
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
            $order->customerEmail(),
            $order->status()->value(),
            $items,
            [
                'cents' => $order->totalCents(),
                'currency' => $order->items()[0]->unitPrice()->currency(),
            ],
            $order->createdAt()->format(DateTimeInterface::ATOM),
            self::address($order->shippingAddress()),
            self::address($order->billingAddress()),
            self::shipping($order->shipping(), $order->items()[0]->unitPrice()->currency()),
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
     *     createdAt: string,
     *     shippingAddress: array{recipient: string, line1: string, line2: string|null, postalCode: string, city: string, country: string}|null,
     *     billingAddress: array{recipient: string, line1: string, line2: string|null, postalCode: string, city: string, country: string}|null,
     *     shipping: array{method: string, label: string, fee: array{cents: int, currency: string}}|null
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customerId,
            'customerEmail' => $this->customerEmail,
            'status' => $this->status,
            'items' => $this->items,
            'total' => $this->total,
            'createdAt' => $this->createdAt,
            'shippingAddress' => $this->shippingAddress,
            'billingAddress' => $this->billingAddress,
            'shipping' => $this->shipping,
        ];
    }

    /**
     * @return array{recipient: string, line1: string, line2: string|null, postalCode: string, city: string, country: string}|null
     */
    private static function address(?PostalAddress $address): ?array
    {
        return $address?->toArray();
    }

    /**
     * @return array{method: string, label: string, fee: array{cents: int, currency: string}}|null
     */
    private static function shipping(?ShippingMethod $shipping, string $currency): ?array
    {
        return $shipping?->toArray($currency);
    }
}
