<?php

declare(strict_types=1);

namespace App\Order\Application\CommandHandler;

use App\Order\Application\Command\MarkOrderPaidCommand;
use App\Order\Domain\Exception\OrderNotFound;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\OrderId;
use App\Shared\Application\Event\OrderMarkedPaid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class MarkOrderPaidCommandHandler
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(MarkOrderPaidCommand $command): void
    {
        $id = OrderId::fromString($command->orderId);
        $order = $this->orderRepository->findById($id);

        if ($order === null) {
            throw new OrderNotFound($id);
        }

        $order->markPaid();
        $this->orderRepository->save($order);

        $this->eventDispatcher->dispatch(new OrderMarkedPaid(
            orderId: $order->id()->value(),
            customerId: $order->customerId()->value(),
        ));
    }
}
