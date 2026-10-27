<?php

declare(strict_types=1);

namespace App\Order\Application\CommandHandler;

use App\Audit\Application\Port\AuditLogger;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditSubjectType;
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
        private ?AuditLogger $audit = null,
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
        $this->audit?->record(AuditAction::PAYMENT_CHANGED, AuditSubjectType::ORDER, $order->id()->value(), [
            'from' => 'pending',
            'to' => 'paid',
        ]);

        $this->eventDispatcher->dispatch(new OrderMarkedPaid(
            orderId: $order->id()->value(),
            customerId: $order->customerId()->value(),
        ));
    }
}
