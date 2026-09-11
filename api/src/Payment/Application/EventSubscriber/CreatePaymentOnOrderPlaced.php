<?php

declare(strict_types=1);

namespace App\Payment\Application\EventSubscriber;

use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Domain\ValueObject\MoneyAmount;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Payment\Domain\ValueObject\PaymentId;
use App\Shared\Application\Event\OrderPlaced;
use App\Shared\Domain\Clock;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CreatePaymentOnOrderPlaced implements EventSubscriberInterface
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private Clock $clock,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [OrderPlaced::class => 'onOrderPlaced'];
    }

    public function onOrderPlaced(OrderPlaced $event): void
    {
        $orderId = OrderReference::fromString($event->orderId);

        if ($this->paymentRepository->findByOrderId($orderId) !== null) {
            return;
        }

        $payment = Payment::createPending(
            PaymentId::generate(),
            $orderId,
            CustomerReference::fromString($event->customerId),
            MoneyAmount::fromCents($event->amountCents),
            $this->clock->now(),
        );

        $this->paymentRepository->save($payment);
    }
}
