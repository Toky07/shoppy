<?php

declare(strict_types=1);

namespace App\Order\Application\EventSubscriber;

use App\Order\Application\Command\MarkOrderPaidCommand;
use App\Order\Application\CommandHandler\MarkOrderPaidCommandHandler;
use App\Order\Domain\Exception\InvalidOrderTransition;
use App\Order\Domain\Exception\OrderNotFound;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\OrderId;
use App\Shared\Application\Event\PaymentCompleted;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class MarkOrderPaidOnPaymentCompleted implements EventSubscriberInterface
{
    public function __construct(
        private OrderRepository $orderRepository,
        private MarkOrderPaidCommandHandler $markOrderPaid,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [PaymentCompleted::class => 'onPaymentCompleted'];
    }

    public function onPaymentCompleted(PaymentCompleted $event): void
    {
        $orderId = OrderId::fromString($event->orderId);
        $order = $this->orderRepository->findById($orderId);

        if ($order === null) {
            throw new OrderNotFound($orderId);
        }

        if ($order->customerId()->value() !== $event->customerId) {
            return;
        }

        if (!$order->status()->isPending()) {
            return;
        }

        try {
            $this->markOrderPaid->handle(new MarkOrderPaidCommand($event->orderId));
        } catch (InvalidOrderTransition) {
            // Concurrent status change — ignore.
        }
    }
}
