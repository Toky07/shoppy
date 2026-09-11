<?php

declare(strict_types=1);

namespace App\Payment\Application\EventSubscriber;

use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Shared\Application\Event\OrderCancelled;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CancelPaymentOnOrderCancelled implements EventSubscriberInterface
{
    public function __construct(private PaymentRepository $paymentRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [OrderCancelled::class => 'onOrderCancelled'];
    }

    public function onOrderCancelled(OrderCancelled $event): void
    {
        $payment = $this->paymentRepository->findByOrderId(
            OrderReference::fromString($event->orderId),
        );

        if ($payment === null) {
            return;
        }

        $payment->cancel();
        $this->paymentRepository->save($payment);
    }
}
