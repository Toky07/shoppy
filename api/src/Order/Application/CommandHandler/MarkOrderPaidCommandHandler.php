<?php

declare(strict_types=1);

namespace App\Order\Application\CommandHandler;

use App\Order\Application\Command\MarkOrderPaidCommand;
use App\Order\Domain\Exception\OrderNotFound;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\OrderId;

final readonly class MarkOrderPaidCommandHandler
{
    public function __construct(private OrderRepository $orderRepository)
    {
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
    }
}
