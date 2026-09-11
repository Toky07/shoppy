<?php

declare(strict_types=1);

namespace App\Order\Application\CommandHandler;

use App\Order\Application\Catalog;
use App\Order\Application\Command\CancelOrderCommand;
use App\Order\Domain\Exception\OrderAccessForbidden;
use App\Order\Domain\Exception\OrderNotFound;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\OrderId;
use App\Shared\Application\Event\OrderCancelled;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class CancelOrderCommandHandler
{
    public function __construct(
        private OrderRepository $orderRepository,
        private Catalog $catalog,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(CancelOrderCommand $command): void
    {
        $id = OrderId::fromString($command->orderId);
        $order = $this->orderRepository->findById($id);

        if ($order === null) {
            throw new OrderNotFound($id);
        }

        if ($order->customerId()->value() !== $command->customerId) {
            throw new OrderAccessForbidden();
        }

        $order->cancel();

        foreach ($order->items() as $item) {
            $this->catalog->increaseStock(
                $item->catalogProductId(),
                $item->quantity()->value(),
            );
        }

        $this->orderRepository->save($order);

        $this->eventDispatcher->dispatch(new OrderCancelled(
            orderId: $order->id()->value(),
            customerId: $order->customerId()->value(),
        ));
    }
}
