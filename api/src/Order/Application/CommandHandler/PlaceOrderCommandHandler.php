<?php

declare(strict_types=1);

namespace App\Order\Application\CommandHandler;

use App\Order\Application\Catalog;
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
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Shared\Application\Event\OrderPlaced;
use App\Shared\Domain\Clock;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class PlaceOrderCommandHandler
{
    public function __construct(
        private OrderRepository $orderRepository,
        private Catalog $catalog,
        private Clock $clock,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(PlaceOrderCommand $command): OrderId
    {
        $items = array_map(
            fn (PlaceOrderLine $line): OrderItem => $this->snapshot($line),
            $command->items,
        );

        foreach ($this->aggregatedQuantities($command->items) as $productId => $quantity) {
            $this->catalog->decreaseStock(CatalogProductId::fromString($productId), $quantity);
        }

        $order = Order::place(
            OrderId::generate(),
            CustomerId::fromString($command->customerId),
            $items,
            $this->clock->now(),
        );

        $this->orderRepository->save($order);

        $this->eventDispatcher->dispatch(new OrderPlaced(
            orderId: $order->id()->value(),
            customerId: $order->customerId()->value(),
            amountCents: $order->totalCents(),
        ));

        return $order->id();
    }

    private function snapshot(PlaceOrderLine $line): OrderItem
    {
        $productId = CatalogProductId::fromString($line->productId);
        $snapshot = $this->catalog->findById($productId);

        if ($snapshot === null) {
            throw new CatalogProductNotFound($productId);
        }

        return OrderItem::of(
            $productId,
            OrderedProductName::fromString($snapshot->name),
            UnitPrice::fromCents($snapshot->unitPriceCents),
            Quantity::fromInt($line->quantity),
        );
    }

    /**
     * @param list<PlaceOrderLine> $items
     *
     * @return array<string, int>
     */
    private function aggregatedQuantities(array $items): array
    {
        $quantities = [];

        foreach ($items as $item) {
            $quantities[$item->productId] = ($quantities[$item->productId] ?? 0) + $item->quantity;
        }

        return $quantities;
    }
}
