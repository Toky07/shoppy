<?php

declare(strict_types=1);

namespace App\Order\Application\CommandHandler;

use App\Order\Application\Catalog;
use App\Order\Application\Command\PlaceOrderAddress;
use App\Order\Application\Command\PlaceOrderCommand;
use App\Order\Application\Command\PlaceOrderLine;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\Exception\CatalogProductNotFound;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\PostalAddress;
use App\Order\Domain\ValueObject\ShippingMethod;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Shared\Application\Event\OrderPlaced;
use App\Shared\Application\Transaction\TransactionRunner;
use App\Shared\Domain\Clock;

final readonly class PlaceOrderCommandHandler
{
    public function __construct(
        private OrderRepository $orderRepository,
        private Catalog $catalog,
        private Clock $clock,
        private TransactionRunner $transactions,
    ) {
    }

    public function handle(PlaceOrderCommand $command): OrderId
    {
        return $this->transactions->run(function () use ($command): OrderId {
            return $this->place($command);
        });
    }

    private function place(PlaceOrderCommand $command): OrderId
    {
        $shippingAddress = $this->address($command->shippingAddress, 'shippingAddress');
        $billingAddress = $this->address($command->billingAddress, 'billingAddress');

        $items = array_map(
            fn (PlaceOrderLine $line): OrderItem => $this->snapshot($line),
            $command->items,
        );
        $shipping = ShippingMethod::quote($command->shippingMethod, $this->merchandiseCents($items));

        foreach ($this->aggregatedQuantities($command->items) as $line) {
            $this->catalog->decreaseStock(
                CatalogProductId::fromString($line['productId']),
                $line['quantity'],
                $line['variantId'],
            );
        }

        $order = Order::place(
            OrderId::generate(),
            CustomerId::fromString($command->customerId),
            $items,
            $this->clock->now(),
            $shippingAddress,
            $billingAddress,
            $shipping,
        );

        $this->orderRepository->save($order);

        $this->transactions->afterCommit(new OrderPlaced(
            orderId: $order->id()->value(),
            customerId: $order->customerId()->value(),
            amountCents: $order->totalCents(),
        ));

        return $order->id();
    }

    /**
     * @param list<OrderItem> $items
     */
    private function merchandiseCents(array $items): int
    {
        return array_reduce(
            $items,
            static fn (int $total, OrderItem $item): int => $total + $item->lineTotalCents(),
            0,
        );
    }

    private function address(PlaceOrderAddress $address, string $prefix): PostalAddress
    {
        return PostalAddress::fromInput(
            $address->recipient,
            $address->line1,
            $address->line2,
            $address->postalCode,
            $address->city,
            $address->country,
            $prefix,
        );
    }

    private function snapshot(PlaceOrderLine $line): OrderItem
    {
        $productId = CatalogProductId::fromString($line->productId);
        $snapshot = $this->catalog->findById($productId, $line->variantId);

        if ($snapshot === null) {
            throw new CatalogProductNotFound($productId);
        }

        return OrderItem::of(
            $productId,
            OrderedProductName::fromString($snapshot->name),
            UnitPrice::fromCents($snapshot->unitPriceCents),
            Quantity::fromInt($line->quantity),
            $line->variantId,
        );
    }

    /**
     * @param list<PlaceOrderLine> $items
     *
     * @return list<array{productId: string, variantId: string|null, quantity: int}>
     */
    private function aggregatedQuantities(array $items): array
    {
        $quantities = [];

        foreach ($items as $item) {
            $key = $item->productId."\0".($item->variantId ?? '');

            if (!isset($quantities[$key])) {
                $quantities[$key] = [
                    'productId' => $item->productId,
                    'variantId' => $item->variantId,
                    'quantity' => 0,
                ];
            }

            $quantities[$key]['quantity'] += $item->quantity;
        }

        return array_values($quantities);
    }
}
